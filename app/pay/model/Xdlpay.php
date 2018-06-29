<?php
namespace app\pay\model;
use think\Model;
class Xdlpay extends Model
{
    private $opSys; // 操作系统 0：ANDROID 1：IOS 2：windows 3:直连
    private $payModel;
    private $url;
    private $characterSet = '00'; // 字符集
    private $signType = 'MD5'; // 签名方式
    private $version = 'V1.0.1'; // 签名方式
    private $pubVersion = 'V1.0.0'; // 签名方式
    private $orgNo; // 机构号7170
    private $mercId; // 商户号
    private $trmNo; // 终端设备号
    private $signKey; // 密钥
    private $cate_data; // 密钥
    private $server = 'http://gateway.starpos.com.cn/adpweb/ehpspos3/';//http://139.196.77.69:8280/adpweb/ehpspos3/ http://gateway.starpos.com.cn/adpweb/ehpspos3/
    private $remark;
    private $checker_id;
    private $jmt_remark;
    private $auth_code;
    private $pay_type;
    private $price;
    private $mode;
    private $channel;
    private $rate;
    private $id;

    public function __construct()
    {
        parent::__construct();
        $this->payModel = db('pay');
        $this->orgNo = '7170';//7170
        $this->mercId = '800290000005310';//800290000005310
        $this->trmNo = '95066032';//95066032
        $this->signKey = 'E29B72D4F4D1EFE145FC132C933DE9ED';//E29B72D4F4D1EFE145FC132C933DE9ED
    }

    public function scan()
    {
        $this->url = $this->server . 'sdkBarcodePay.json';
        $params = $this->requestHead();
        $params['tradeNo'] = $this->getRemark();
        $order_sn = $params['tradeNo'];
        $params['amount'] = 1;
        $params['total_amount'] = 1;
        $params['authCode'] = I('code', '134676661255380713');
        $params['payChannel'] = 'WXPAY'; //支付宝	ALIPAY 微信	WXPAY 银联	YLPAY
        $params['signValue'] = $this->getSign($params);
        $this->writlog('micro.log', 'payParams：' . json_encode($params));
        $return = $this->requestPost(json_encode($params));
        $result = json_decode(urldecode($return), true);
        dump($result);
        if ($result['returnCode'] == '000000' && $result['result'] == 'S') {
            $this->writlog('micro.log', '支付成功：' . urldecode($return));
            return array("flag" => true, "msg" => "成功", "data" => '支付成功');
        } else if ($result['returnCode'] == '000000' && ($result['result'] = "A" || $result['result'] = "Z")) {
            $this->writlog('micro.log', '输入密码：' . urldecode($return));
            $this->password($order_sn);
        } else {
            $this->writlog('micro.log', '支付失败：' . urldecode($return));
            return array("flag" => false, "msg" => "失败", "data" => $result['message']);
        }
    }

    public function test()
    {
        $merchant_id = I('mid');
        $this->getInfo($merchant_id);
        $this->url = $this->server . 'pubSigQry.json';
        $header['orgNo'] = $this->orgNo;
        $header['mercId'] = $this->mercId;
        $header['trmNo'] = $this->trmNo;
        $header['txnTime'] = date('YmdHis');
        $header['signType'] = $this->signType;
        $header['version'] = $this->pubVersion;
        $header['signValue'] = $this->getSign($header);

        $this->writlog('pubsig.log', 'Params：' . json_encode($header));
        $return = $this->requestPost(json_encode($header));
        $result = json_decode(urldecode($return), true);
        if ($result['returnCode'] == '000000') {
            $this->writlog('pubsig.log', '成功：' . urldecode($return));
            return array("flag" => true, "msg" => "成功", "data" => '支付成功');
        } else {
            $this->writlog('pubsig.log', '失败：' . urldecode($return));
            return array("flag" => false, "msg" => "失败", "data" => $result['message']);
        }
    }

    public function get_openid($info)
    {
        //这里直接获得openid;
        $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SESSION['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirect_uri = urlencode($redirect_uri);
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $info['appId'] . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=snsapi_base#wechat_redirect';
        redirect($url);
    }

    # APP_支付宝_条形码支付
    public function ali_micropay($id, $price, $auth_code, $checker_id, $jmt_remark,$order_sn,$cost_rate)
    {

        $this->checker_id = $checker_id;
        $this->jmt_remark = $jmt_remark;
        $this->auth_code = $auth_code;
        $this->remark = $order_sn?:$this->getRemark();
        $this->pay_type = 2;
        $this->price = $price;
        $this->mode = 15;
        $this->id = $id;
        $this->rate = $cost_rate;
        $this->channel = 'ALIPAY';

        return $this->micropay();
    }

    # APP_微信_条形码支付
    public function wx_micropay($id, $price, $auth_code, $checker_id,$jmt_remark,$order_sn,$cost_rate)
    {
        $this->checker_id = $checker_id;
        $this->jmt_remark = $jmt_remark;
        $this->auth_code = $auth_code;
        $this->remark = $order_sn?:$this->getRemark();
        $this->pay_type = 1;
        $this->price = $price;
        $this->mode = 15;
        $this->id = $id;
        $this->rate = $cost_rate;
        $this->channel = 'WXPAY';

        return $this->micropay();
    }

    private function micropay()
    {
        $this->url = $this->server . 'sdkBarcodePay.json';
        if (!$this->auth_code || !$this->id || $this->price < 0.01) $this->error('参数错误');

        $this->cate_data = db('merchants_cate')->where(array('merchant_id'=>$this->id,'status'=>1))->find();
        $this->mercId = db('merchants_xdl')->where(array('m_id' => $this->cate_data['merchant_id']))->value('mercId');
        $this->getInfo($this->id);
        $add_res = $this->add();
        if ($add_res) {
            $params = $this->requestHead();
            $params['tradeNo'] = $this->remark;
            $params['amount'] = $this->price * 100;
            $params['total_amount'] = $this->price * 100;
            $params['authCode'] = $this->auth_code;
            $params['payChannel'] = $this->channel; //支付宝	ALIPAY 微信	WXPAY 银联	YLPAY
            $params['signValue'] = $this->getSign($params);
            $this->writlog('micro.log', 'payParams：' . json_encode($params));
            //yj_log('xdl',$params);
            $return = $this->requestPost(json_encode($params));
            $result = json_decode(urldecode($return), true);
            $this->writlog('micro.log', 'resParams：' . json_encode($result));
            //yj_log('xdl',$result);
            if ($result['result'] == 'S') {
                //$this->writlog('micro.log', '支付成功：' . urldecode($return));
                $save = array(
                    "status" => "1",
                    "paytime" => time(),
                    'transId' => $result['logNo'],
                    'new_order_sn' => $result['orderNo'],
                );
                $this->payModel->where("remark" , $this->remark)->update($save);
//                A("App/PushMsg")->push_pay_message($order_sn);
                return ['transaction_id'=>$result['orderNo'],'price'=>$this->price * 100];
            } else if ($result['result'] = "A" || $result['result'] = "Z") {
                $this->writlog('micro.log', '输入密码：' . urldecode($return));
                $res =  $this->password($this->remark);
                if($res['code'] == 'success'){
                    return ['transaction_id'=>$result['orderNo'],'price'=>$this->price * 100];
                }else{
                    return $this->error($res['data']);
                }
            } else {
                $this->writlog('micro.log', '支付失败：' . urldecode($return));
                return $this->error($result['message']);
            }
        }
    }

    public function ali_pay()
    {
//        $this->getInfo(84);
        $params = $this->requestHead();
        $params['tradeNo'] = $this->remark;
        $params['amount'] = $this->price * 100;
        $params['total_amount'] = $this->price * 100;
        $params['payChannel'] = $this->channel; //支付宝	ALIPAY 微信	WXPAY 银联	YLPAY
        $params['signValue'] = $this->getSign($params);
        $this->writlog('JS_ali_pay.log', 'payParams：' . json_encode($params));
        $return = $this->requestPost(json_encode($params));
        $result = json_decode(urldecode($return), true);
        $this->writlog('JS_ali_pay.log', 'payParams：' . json_encode($result));

        return $result;
    }

    # 查询订单状态
    public function query($order_sn)
    {
        $this->url = $this->server . 'sdkQryBarcodePay.json';
        $params = $this->requestHead();
        $params['tradeNo'] = $this->getRemark();
        $params['qryNo'] = $order_sn;
        $params['signValue'] = $this->getSign($params);
        $result = $this->requestPost(json_encode($params));
        $this->writlog('micro.log', 'queryResult：' . urldecode($result));

        return json_decode(urldecode($result), true);
    }

    # 撤销订单
    public function reverse($order_sn)
    {
        $this->url = $this->server . 'RevokeBarcodepay.json';
        $params = $this->requestHead();
        $params['tradeNo'] = $this->getRemark();
        $params['qryNo'] = $order_sn;
        $params['signValue'] = $this->getSign($params);
        $result = $this->requestPost(json_encode($params));
        $this->writlog('micro.log', 'cancelResult：' . urldecode($result));

        return json_decode(urldecode($result), true);
    }

    public function pay_back($remark, $price_back)
    {
        $pay = db("pay")->where("remark" , $remark)->find();
        if (!$pay) {
            return array("code" => 'error', "msg" => "该订单不存在");
        }
        $back_order = $pay['new_order_sn'];
        if ($pay['status'] == "2") {
            return array("code" => 'error', "msg" => "不能重复退款");
        }
        $merchant_id = $pay['merchant_id'];
        $res = db("merchants_cate")->where("merchant_id=$merchant_id")->find();
        if (!$res) {
            return array("code" => 'error', "msg" => "商户不存在");
        }
        $this->getInfo($merchant_id);
        $this->url = $this->server . 'sdkRefundBarcodePay.json';
        $params = $this->requestHead();
        $params['tradeNo'] = $this->getRemark();
        $params['orderNo'] = $back_order;
        $params['txnAmt'] = $price_back * 100;
        $params['signValue'] = $this->getSign($params);
        $this->writlog('payback.log', $merchant_id.'backParams：' . json_encode($params));
        $return = $this->requestPost(json_encode($params));
        $result = json_decode(urldecode($return), true);
        if ($result['returnCode'] == '000000' && $result['result'] == 'S') {
            db("pay")->where("remark='$remark'")->update(array("status" => 2, "back_status" => 1, "price_back" => $result['txnAmt'] / 100));
            $this->writlog('payback.log', '退款成功：' . urldecode($return));
            return array("code" => "success", "msg" => "成功", "data" => "退款成功");
        } else {
            $this->writlog('payback.log', '退款失败：' . urldecode($return));
            return array("code" => "error", "msg" => "error", "data" => "退款失败");
        }
    }

    # 获取台签信息
    private function get_cate_info($id)
    {
        $res = db('merchants_cate')->where(array('id'=>$id))->find();
        return $res;
    }

    # 轮询条码支付订单
    public function password($order_sn)
    {
        $queryTimes = 8;
        while ($queryTimes--) {
            sleep(5);
            $queryRes = $this->query($order_sn);
            if ($queryRes['returnCode'] == '000000') {
                $result = $queryRes['result'];
                if ($result == 'S') {   // 支付成功
                    $brr = array("status" => "1", "paytime" => time(), "customer_id" => '', 'transId' => $queryRes['logNo'], 'new_order_sn' => $queryRes['orderNo'],);
                    $this->writlog('micro.log', 'querySucc：' . json_encode($queryRes));
                    $this->payModel->where("remark" , $order_sn)->update($brr);
                    return array("code" => "success", "msg" => "成功");
                } else if ($result == 'A') {    // 等待密码
                    continue;
                } else if ($result == 'Z') {    // 未知状态
                    continue;
                } else if ($result == 'F') {    // 支付失败
                    $this->payModel->where(array("remark" => $order_sn))->update(array("status" => "-2"));
                    return array("code" => "error", "msg" => "失败", "data" => '支付失败');
                } else if ($result == 'D') {    // 已撤销
                    $this->payModel->where(array("remark" => $order_sn))->update(array("status" => "-2"));
                    return array("code" => "error", "msg" => "失败", "data" => '支付失败');
                }
            } else {
                $this->writlog('micro.log', '请求失败：' . json_encode($queryRes));
                $this->payModel->where(array("remark" => $order_sn))->update(array("status" => "-2"));
                return array("code" => "error", "msg" => "失败", "data" => '支付失败');
            }
        }
        $res = $this->reverse($order_sn);
        sleep(3);
        if ($res['result'] == 'S') {
            $this->payModel->where(array("remark" => $res['out_trade_no']))->update(array("status" => "-2"));
            return array("code" => "error", "msg" => "失败", "data" => '交易时间过长,支付结果请以客户支付成功界面为准');
        }
        return array("code" => "error", "msg" => "失败", "data" => '交易时间过长,支付结果请以客户支付成功界面为准');
    }

    # 将订单插入数据库
    private function add()
    {
        //插入数据库的数据
        $data['merchant_id'] = $this->cate_data['merchant_id'];//商户ID
        //$data['phone_info'] = $_SERVER['HTTP_USER_AGENT'];
        $data['customer_id'] = $this->mercId;              //买方账号ID
        $data['buyers_account'] = '';              //买方账号ID
        $data['checker_id'] = $this->checker_id;              //收银员的ID
        $data['paystyle_id'] = $this->pay_type;               //支付方式 1是微信 2是支付宝
        $data['price'] = $this->price;
        $data['remark'] = $this->remark;                    //订单号
        $data['status'] = 0;                            //待付款
        $data['cate_id'] = $this->cate_data['id'];                  //支付样式,台签类别
        $data['mode'] = 15;                              //0 为台签支付 1为扫码支付  2刷卡支付
        $data['jmt_remark'] = $this->jmt_remark;                              //0 为台签支付 1为扫码支付  2刷卡支付
        $data['add_time'] = time();                     //下单时间
        $data['subject'] = "向" . $this->cate_data['jianchen'] . "支付" . $this->price . "元";
        $data['bank'] = 11;
        $data['cost_rate'] = $this->rate;
        $return = db('pay')->insert($data);
        return $return;
    }

    private function getInfo($merchant_id)
    {
        $re = db('merchants_xdl')->where(array('m_id' => $merchant_id))->find();
        $this->orgNo = $re['orgNo'];
        $this->mercId = $re['mercId'];
        $this->trmNo = $re['trmNo'];
        $this->signKey = $re['signKey'];
    }

    # 发送请求
    private function requestPost($data, $second = 30)
    {
        $header = array("Content-type:application/json;charset=UTF-8");
        //初始化curl
        $curl = curl_init();
        //设置超时
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //post提交方式
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl
        $res = curl_exec($curl);
        //返回结果
        if ($res) {
            curl_close($curl);
            return $res;
        } else {
            $error = curl_errno($curl);
            $this->writlog('request.log', 'ERROR：' . "curl出错，错误码:$error");
            curl_close($curl);
            return false;
        }
    }

    # 组织请求头部参数
    private function requestHead()
    {
        $header = array();
        $header['opSys'] = '0';
        $header['orgNo'] = $this->orgNo;
        $header['characterSet'] = $this->characterSet;
        $header['mercId'] = $this->mercId;
        $header['trmNo'] = $this->trmNo;
        $header['txnTime'] = date('YmdHis');
        $header['signType'] = $this->signType;
        $header['version'] = $this->version;

        return $header;
    }

    private function getRemark()
    {
        return date('YmdHis') . rand(100000, 999999);
    }

    private function getSign($params)
    {
        ksort($params);
        $str = '';
        foreach ($params as $v) {
            $str .= $v;
        }

        return md5($str . $this->signKey);
    }

    # 错误信息展示
    private function alert_err($msg = '网络异常，请重试！')
    {
        $this->assign('err_msg',"$msg");
        $this->display(":Barcodexybank/error");
    }

    private function writlog($file_name, $data)
    {
        $path = $this->get_date_dir();
        file_put_contents($path . $file_name, date("H:i:s") . $data . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function get_date_dir($path = '/log/xindalu/')
    {
        $Y = $_SERVER['DOCUMENT_ROOT'] . $path . date("Y-m");
        $d = $Y . '/' . date("m-d");
        if (file_exists($Y)) {
//            echo '存在';
        } else {
            mkdir($Y, 0777, true);
        }
        if (!file_exists($d)) mkdir($d, 0777);

        return $d . '/';
    }
    public function error($msg){
        $this->error = $msg;
        return false;
    }
}