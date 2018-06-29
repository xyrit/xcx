<?php
namespace app\pay\model;
use think\Model;
use Lib\Subtable;
class Lspay extends Model
{
    private $url;
    private $notify_url;
    private $remark;
    private $mch_id;
    private $key;
    private $subject='';
    private $order_id=0;
    private $customer_id=0;
    private $paystyle_id;
    private $cate_id = 0;
    private $rate;
    private $is_t=0;
    private $client_ip;

    function __construct()
    {
        parent::__construct();
        $this->url = "https://mobilepos.yeahka.com/cgi-bin/lepos_pay_gateway.cgi";
        $this->notify_url = "https://sy.youngport.com.cn/notify/leshua_notify.php";
        $this->mch_id = '9307002285';
        $this->key = 'FBF50AD4E24183AD42DD5F259200FDB7';
    }

    //public function wx_micropay($id, $price, $auth_code, $checker_id,$jmt_remark,$order_sn)
    public function wx_micropay($id, $price, $auth_code, $checker_id,$jmt_remark,$order_sn)
    {
        $this->paystyle_id = 1;
        $this->pay_way = 'WXZF';
        $this->subject = "支付{$price}元";
        $this->getIntoInfo($id,1);
        if($order_sn){
            $this->remark = $order_sn;
        } else {
            $this->remark = date('YmdHis') . rand(100000, 999999);
        }
        return $this->micropay($id, $price, $auth_code, $checker_id,$jmt_remark);
    }

    public function ali_micropay($id, $price, $auth_code, $checker_id, $jmt_remark,$order_sn)
    {
        $this->paystyle_id = 2;
        $this->pay_way = 'ZFBZF';
        $this->subject = "支付{$price}元";
        $this->getIntoInfo($id,2);
        if($order_sn){
            $this->remark = $order_sn;
        } else {
            $this->remark = date('YmdHis') . rand(100000, 999999);
        }

        return $this->micropay($id, $price, $auth_code, $checker_id,$jmt_remark,$order_sn);
    }

    private function micropay($id, $price, $auth_code, $checker_id,$jmt_remark)
    {
        //插入数据库的数据
        $db_res = $this->add_db($id,$price,$checker_id,$jmt_remark);
        //$error = array("code" => "error", "msg" => "失败", "data" => '请重试');
        if($db_res){
            $res_arr = $this->post_micropay($price,$auth_code);
            if ($res_arr['resp_code']=='0'&&$res_arr['result_code']=='0') {
                if($res_arr['status']=='0'){
                    $this->writeLog('micro.log', ':输入密码,返回',$res_arr);
                    return $this->password($this->remark);
                }else if($res_arr['status']=='2'){
                    $this->writeLog('micro.log', ':支付成功,返回',$res_arr);
                    db(Subtable::getSubTableName('pay'))->where(array("remark" => $this->remark))
                        ->update(array("status" => "1", "paytime" => time(), 'transId' => $res_arr['leshua_order_id']));
                    return ['transaction_id'=>$res_arr['leshua_order_id'],'price'=>$price];
                    //return array("code" => "success", "msg" => "成功", "data" => '支付成功');
                } else {
                    $this->writeLog('micro.log', ':支付失败1,返回',$res_arr);
                    return $this->error('支付失败');
                }
            } else {
                $this->writeLog('micro.log', ':支付失败2,返回',$res_arr);
                return $this->error('支付失败');
            }
        } else{
            $this->writeLog('micro.log', ':db_res_err',array());
            return $this->error('支付失败');
        }
    }

    public function notify()
    {
        $data = file_get_contents('php://input');
        $result_arr = $this->xmlToArray($data);
        if ($result_arr['error_code'] == '0' && $result_arr['status'] == '2') {
            $order_sn = $result_arr['third_order_id'];
            $transId = $result_arr['leshua_order_id'];
            $orderData = db(Subtable::getSubTableName('pay'))->where(array('remark' => $order_sn))->find();
            if ($orderData['status'] == 0) {
                $save['transId'] = $transId;
                $save['paytime'] = time();
                $save['status'] = 1;
                if(bccomp($orderData['price']*100, $result_arr['amount'], 3) === 0){
                    db(Subtable::getSubTableName('pay'))->where(array('id'=>$orderData['id']))->update($save);
                    $this->writeLog('notify.log', ':支付成功',$result_arr);
                } else {
                    $this->writeLog('notify.log', ':金额不等',$result_arr);
                }
            } else if($orderData['status'] == 1){
                $this->writeLog('notify.log', ':二次通知',$result_arr);
                exit("000000");
            } else {
                $this->writeLog('notify.log', ':订单状态异常',$result_arr);
                echo "error";
            }
        }else {
            $this->writeLog('notify.log', ':支付失败',$data);
            echo "error";
        }
    }

    public function mch_notify()
    {
        header("Content-Type: application/json");
        $this->writeLogA('mch_notify.log', ':通知数据$_POST',$_POST);
        $sParam = $_POST['sParam']?:'';
        if(empty($sParam)){
            $return['bResult'] = false;
            $return['errMsg'] = 'data is null';
            $this->writeLogA('mch_notify.log', ':数据未收到','null',0);
            exit(json_encode($return));
        }
        $notifyData = json_decode($sParam,true);
        $this->writeLogA('mch_notify.log', 'Reason',$notifyData['sFailReason'],0);
        if($notifyData['sStatus'] == 0){
            $data['update_status'] = 3;
            db('merchants_leshua')->where(array('merchantId'=>$notifyData['sMerchantld']))->update($data);
            $return['bResult'] = true;
            $return['errMsg'] = 'null';
            $this->writeLogA('mch_notify.log', ':修改成功',$notifyData);
            exit(json_encode($return));
        } else {
            $data['update_status'] = 1;
            $data['err_msg'] = $notifyData['sFailReason'];
            db('merchants_leshua')->where(array('merchantId'=>$notifyData['sMerchantld']))->update($data);
            $return['bResult'] = true;
            $return['errMsg'] = 'status error';
            $this->writeLogA('mch_notify.log', ':修改失败',$notifyData);
            exit(json_encode($return));
        }
    }

    // 查询订单状态
    public function query($remark)
    {
        $pay_info = db(Subtable::getSubTableName('pay'))->alias('p')
            ->field('p.transId,ls.merchantId,ls.key')
            ->join('ypt_merchants_leshua ls','p.merchant_id=ls.m_id','LEFT')
            ->where(array('remark' => $remark))
            ->find();
        $this->mch_id = $pay_info['merchantId'];
        $this->key = $pay_info['key'];
        $data['merchant_id'] = $this->mch_id;//商户号
        $data['service'] = 'query_status';
        $data['third_order_id'] = $remark;//商户系统内部的订单号
        $data['nonce_str'] = $this->getNonceStr();//UCHANG订单号，优先使用
        $data['sign'] = $this->getSignVeryfy($data, $this->key);
        $this->writeLog('query.log', ':参数', $data);
        $res = $this->httpRequst($this->url, $data);
        $res_arr = $this->xmlToArray($res);

        return $res_arr;
    }

    public function myback()
    {
        $param['service'] = 'refund';
        $param['merchant_id'] = $this->mch_id;
        $param['third_order_id'] = I('remark');
        $param['leshua_order_id'] = I('san');
        $param['nonce_str'] = $this->getNonceStr();
        $param['sign'] = $this->getSignVeryfy($param, $this->key);
        $this->writeLog('refund.log', ':参数', $param);
        $res = $this->httpRequst($this->url, $param);
        $res_arr = $this->xmlToArray($res);
        $this->writeLog('refund.log', ':参数', $res_arr);
        $this->ajaxReturn($res_arr);
    }

    /**
     * 获取进件信息
     * @param $mch_id 洋仆淘商户ID
     * @param $way  支付方式1-微信,2-支付宝
     */
    private function getIntoInfo($mch_id,$way)
    {
        $into_data = db('merchants_leshua')->where("m_id=$mch_id")->find();
        $this->mch_id = $into_data['merchantId'];
        $this->key = $into_data['key'];
        $this->is_t = $into_data['is_t0'];
        $this->client_ip = $into_data['ip_address']?:$_SERVER['REMOTE_ADDR'];//IP
        if($way == 1){
            $this->rate=$into_data['is_t0']==1?$into_data['wx_t0_rate']:$into_data['wx_t1_rate'];
        } else {
            $this->rate=$into_data['is_t0']==1?$into_data['ali_t0_rate']:$into_data['ali_t1_rate'];
        }
    }

    private function check_sign($data)
    {
        if(isset($data['sign'])){
            $sign = $data['sign'];
            unset($data['sign']);
            unset($data['resp_code']);
            $new_sign = $this->getSignVeryfy($data, $this->key);
            if($sign == $new_sign){
                return true;
            }
        }
        return false;
    }

    private function post_micropay($price,$auth_code)
    {
        $param['service'] = 'upload_authcode';
        $param['pay_way'] = $this->pay_way;
        $param['merchant_id'] = $this->mch_id;//商户号
        $param['third_order_id'] = $this->remark;//商户订单号
        $param['amount'] = (int)($price * 100);//金额
        $param['client_ip'] = $this->client_ip;
        $param['t0'] = $this->is_t;
        $param['notify_url'] = $this->notify_url;
        $param['auth_code'] = $auth_code;
        $param['nonce_str'] = $this->getNonceStr();//随机字符串
        $param['sign'] = $this->getSignVeryfy($param, $this->key);//签名
        $this->writeLog('micro.log', ':参数', $param);
        $res = $this->httpRequst($this->url, $param);
        $res_arr = $this->xmlToArray($res);

        return $res_arr;
    }

    private function add_db($id,$price,$checker_id,$jmt_remark)
    {
        $data = array(
            'merchant_id' => $id,
            'order_id' => $this->order_id,
            'customer_id' => $this->customer_id,
            'buyers_account' => '',
            'phone_info' => '',
            'wx_remark' => '',
            'wz_remark' => '',
            'new_order_sn' => '',
            'no_number' => '',
            'transId' => '',
            'la_ka_la' => 0,
            'add_time' => time(),
            'paytime' => time(),
            'bill_date' => date('Ymd'),
            'checker_id' => $checker_id,
            'paystyle_id' => $this->paystyle_id,
            'price' => $price,
            'remark' => $this->remark,
            'status' => 0,
            'cate_id' => $this->cate_id,
            'mode' => 15,
            'bank' => 12,
            'cost_rate' => $this->rate,
            'subject' => $this->subject,
            'remark_mer' => '',
        );
        $data['jmt_remark']=$jmt_remark?:'';

        return db(Subtable::getSubTableName('pay'))->insert($data);
    }

    private function password($remark)
    {
        $queryTimes = 7;
        while ($queryTimes >= 0) {
            $queryTimes--;
            $query_res = $this->query($remark);
            if ($query_res['resp_code'] == 0 && $query_res['result_code'] == 0) {
                if($query_res['status'] == 0){
                    if($queryTimes == 0){
                        $this->cancel($remark);
                        return $this->error('支付时间超时，已撤销订单');
                    }else{
                        sleep(5);
                        $this->writeLog('query.log', ':继续查询', $query_res);
                        continue;
                    }
                }else if($query_res['status'] == 2){
                    $this->writeLog('query.log', ':支付成功', $query_res);
                    db(Subtable::getSubTableName('pay'))->where(array("remark" => $remark))->update(array("status" => "1", "paytime" => time(), 'transId' => $query_res['leshua_order_id']));
                    return ['transaction_id'=>$query_res['leshua_order_id'],'price'=>$query_res['amount']/100];
                    //return array("code" => "success", "msg" => "支付成功", "data" => '支付成功');
                }else {
                    $this->writeLog('query.log', ':支付失败', $query_res);
                    return $this->error('支付失败');
                    //return array("code" => "error", "msg" => "失败", "data" => '请重试');
                }
            } else {
                return $this->error('支付失败');
                //return array("code" => "error", "msg" => "失败", "data" => '请重试');
            }
        }
        return $this->error('支付失败');
        //return array("code" => "error", "msg" => "失败", "data" => '请重试');
    }

    public function cancel($remark)
    {
        $param['service'] = 'close_order';
        $param['merchant_id'] = $this->mch_id;//商户号
        $param['third_order_id'] = $remark;//商户订单号
        $param['nonce_str'] = $this->getNonceStr();//随机字符串
        $param['sign'] = $this->getSignVeryfy($param, $this->key);//签名
        $this->writeLog('cancel.log', ':参数', $param);
        $res = $this->httpRequst($this->url, $param);
        //$this->writeLog('cancel.log', ':返回1',$res);
        $res_arr = $this->xmlToArray($res);
        $this->writeLog('cancel.log', ':返回',$res_arr);

        return $res_arr;
    }

    //xml转数组
    private function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    //支付接口 curl
    private function httpRequst($url, $post_data)
    {
        $headers = array("Accept-Charset: utf-8");
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
        //显示获得的数据
    }

    //支付接口统一签名
    private function getSignVeryfy($para_temp, $key)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);

        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);

        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);
        //拼接apikey
        $prestr = $prestr . "&key=" . $key;
        //MD5 转大写
        $prestr = strtoupper(md5($prestr));
        return $prestr;
    }

    //除去空字符串
    private function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val === "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    //数组排序
    private function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * @return bool|string 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    /**
     * 获取随机字符串
     * @return string
     */
    private function getNonceStr()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < 32; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return strtoupper($str);
    }

    private function writeLog($file_name, $title, $param, $json=true)
    {
        $path = $this->get_date_dir();
        if($json){
            $param = json_encode($param);
        }
        file_put_contents($path . $file_name, date("Y-m-d H:i:s") . $title.':'. $param . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function get_date_dir($path = '/log/leShua/')
    {
        $Y = $_SERVER['DOCUMENT_ROOT'] . $path . date("Y-m");
        $d = $Y . '/' . date('d');
        if (!file_exists($Y)) mkdir($Y, 0777, true);
        if (!file_exists($d)) mkdir($d, 0777);

        return $d . '/';
    }

    private function writeLogA($file_name, $title, $param, $json=true)
    {
        $path = $this->get_date_dirA();
        if($json){
            $param = json_encode($param);
        }
        file_put_contents($path . $file_name, date("Y-m-d H:i:s") . $title.':'. $param . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function get_date_dirA($path = '/log/leShua/')
    {
        $Y = $_SERVER['DOCUMENT_ROOT'] . $path . date("Y-m");
        if (!file_exists($Y)) mkdir($Y, 0777, true);

        return $Y . '/';
    }
    public function error($msg){
        $this->error = $msg;
        return false;
    }

}