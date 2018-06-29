<?php
include_once("WzPay.pub.config.php");
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/17
 * Time: 16:04
 */

/**
 *
 * 数据对象基础类，该类中定义数据类最基本的行为，包括：
 * 计算/设置/获取签名、输出xml格式的参数、从xml读取数据对象等
 * @author widyhu
 *
 */
class WxPayDataBase
{

    protected $values = array();
    public $memessage = array();
    /**
     * 设置签名，详见签名生成算法
     * @param string $value
     **/
    protected function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;

        return $sign;
    }

    /**
     * 获取签名，详见签名生成算法的值
     * @return 值
     **/
    protected function GetSign()
    {
        return $this->values['sign'];
    }

    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    protected function IsSignSet()
    {
        return array_key_exists('sign', $this->values);
    }

    /**
     *
     * 检测签名
     */
    protected function CheckSign()
    {
        //fix异常
//         file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . json_decode($this->values) . PHP_EOL, FILE_APPEND | LOCK_EX);
        if (!$this->IsSignSet()) {
            exit('微信支付异常');
        }
        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            return true;
        }
//        exit('签名错误!');
//        return 213;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    protected function ToXml()
    {
        if (!is_array($this->values)
            || count($this->values) <= 0
        ) {
            exit('数组数据异常!');
        }

        $xml = "<xml>";
        foreach ($this->values as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    protected function FromXml($xml)
    {
        if (!$xml) {
            exit('xml数据异常!');
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $this->values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");

        return $buff;
    }

    /**
     * 微众银行字符串拼接(coupon_fee=0&orderid=201701041635141483518914460149&payment=0&result={"errmsg":"FAIL,AUTH_CODE_INVALID,101 请扫描微信支付付款码","errno":"1"}&total_fee=0)
     *
     * { "result": { "errmsg": "成功", "errno": "0" }, "orderid"  : "201511111555311447228531021539", "payment"  : "1", "openid"  : "liu", "is_subscribe"  : "Y", "trade_type"  : " MICROPAY", "bank_type"  : "abc", "total_fee"  : "0.01", "coupon_fee"  : "0.00", "fee_type”  : "CNY", "transaction_id" : "1234567890", "time_end"  : "201408261216", "sign"   : "5213f842d5acb8ba7f8e03d4ff470143" }
     */
    protected function wToUrlParams()
    {
        //过滤掉'',null字段,不包括0
        $params = array_filter($this->values, function ($v) {
            if ($v === null || $v === '') {
                return false;
            }
            return true;
        });
        //微众返回的json result字段里面也是一个json直接把里面的json拼接在url上
        $buff = '';
        foreach ($params as $key => $p) {
            if ($key != 'sign' && !is_array($p)) {
                $buff .= $key . '=' . $p . '&';
            }
            if (is_array($p)) {
                //不对中文进行转换
                foreach ($p as $k => $v) {
                    $p[$k] = urlencode($v);
                }
                $buff .= $key . '=' . urldecode(json_encode($p)) . '&';
            }
        }
        $buff = trim($buff, '&');

        return $buff;

    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    protected function MakeSign()
    {
        //签名步骤一：按字典序排序参数
//        echo $this->memessage['key'];
        ksort($this->values);
        $string = $this->wToUrlParams();
        //签名步骤二：在string后加入KEY
//        $string = $string . "&key=" . "326545";
        $string = $string . "&key=" . $this->memessage['key'];
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        //file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . $result . PHP_EOL, FILE_APPEND | LOCK_EX);
        return $result;
    }

    /**
     * 获取设置的值
     */
    protected function GetValues()
    {
        return $this->values;
    }

    /**
     *    作用：设置请求参数
     */
    protected function setParameter($parameter, $parameterValue)
    {
        $this->values[$parameter] = $parameterValue;
    }

    /**
     *    作用：使用证书，以post方式提交xml到对应的接口url
     */
    protected function postXmlSSLCurl($xml, $url, $second = 30)
    {

        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $header = array("Content-Type: application/xml");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, WzPayConf_pub::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, WzPayConf_pub::SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);

            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);

            return false;
        }
    }

    protected function makeOrderSn()
    {
        return date('YmdHis') . substr(time(), -5) . mt_rand(100, 999);
    }

    protected function init($json)
    {
        //初始化values
        $this->values = null;
        $this->values = json_decode($json, true);
//        file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . json_decode($json) . PHP_EOL, FILE_APPEND | LOCK_EX);
        $this->CheckSign();

        return $this->GetValues();
    }

    //    获取key值
    private function getWzKey($mch_id)
    {
        return M('merchants_cate')->where(array('wx_mchid' => $mch_id))->getField('wx_key');
    }
}

/**
 * Class orderQuery 查询订单
 */
class orderQuery extends WxPayDataBase
{
    private $transaction_id=array();

    public function __construct($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    private function setParams()
    {
//        问题所在
        $this->memessage=$this->transaction_id;
        $this->setParameter('merchant_code', $this->transaction_id['merchant_code']);
        $this->setParameter('terminal_code', 'web');
        $this->setParameter('orderid', $this->transaction_id['orderid']);
        $this->SetSign();
        // file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . 11 . PHP_EOL, FILE_APPEND | LOCK_EX);

        $queryData = json_encode($this->values);
        $returnData = $this->postXmlSSLCurl($queryData, WzPayConf_pub::MICROPAY_QUERYORDER);
        return $this->init($returnData);
    }

//  睡觉之后从这里进入
    public function orderQuery()
    {
        //file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . 11 . PHP_EOL, FILE_APPEND | LOCK_EX);
        return $this->setParams();
    }
}

/**
 * Class WxPayReverse 订单撤销
 */
class WxPayReverse extends WxPayDataBase
{
    //订单号
//    private $out_trade_no;
//    订单金额
//    private $amount;
    public $payInfo = array();
    public function __construct($payInfo)
    {
//        $this->out_trade_no = $payInfo['out_trade_no'];
//        $this->amount = $payInfo['amount'];
        $this->payInfo = $payInfo;
    }

    private function setParams()
    {
        $this->memessage=$this->payInfo;
        $this->setParameter('merchant_code', $this->payInfo['merchant_code']);
        $this->setParameter('terminal_code', 'web');
        $this->setParameter('terminal_serialno', $this->payInfo['remark']);
        $this->setParameter('o_terminal_serialno', $this->payInfo['orderid']);
        $this->setParameter('amount', $this->payInfo['pay_money']);

//        $this->setParameter('merchant_code', "107584000030001");
//        $this->setParameter('terminal_code', 'web');
//        $this->setParameter('terminal_serialno', $this->makeOrderSn());
//        $this->setParameter('o_terminal_serialno', $this->out_trade_no);
//        $this->setParameter('amount', $this->amount);
        $this->SetSign();
        $queryData = json_encode($this->values);
        $returnData = $this->postXmlSSLCurl($queryData, WzPayConf_pub::CANCEL_ORDER);
        return $this->init($returnData);

    }

    /**
     * @return mixed 撤销订单
     */
    public function reverse()
    {
        return $this->setParams();
    }


}

/**
 * Class micropay 刷卡支付
 */
class micropay extends WxPayDataBase
{
    //保存订单生成时的订单号
    private $out_trade_no;
    //支付金额
    private $amount;
//    商户的支付信息

    /**
     * micropay constructor.
     * @param $data 刷卡支付金额和用户授权码
     */
    private function setParams($data)
    {
//        $this->setParameter('merchant_code',"107584000030001");
        $this->setParameter('merchant_code',$data['merchant_code']);
        $this->setParameter('terminal_code', 'web');
//        $this->setParameter('terminal_serialno', $this->makeOrderSn());
        $this->setParameter('terminal_serialno', $data['remark']);
        $this->setParameter('amount', $data['pay_money']);
//        $this->setParameter('product', '测试刷卡支付');
        $this->setParameter('product', $data['product']);
        $this->setParameter('auth_code', $data['auth_code']);
        $this->SetSign();
        $queryData = json_encode($this->values);
        //把订单号保留下来用于后续的订单查询
        $this->out_trade_no = $this->values['terminal_serialno'];
        //支付金额也保存下来,因为init()操作会把values值清空,并保存返回值
        $this->amount = $this->values['amount'];
        $returnData = $this->postXmlSSLCurl($queryData, WzPayConf_pub::MICROPAY);
        file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' ."得到数据". $returnData . PHP_EOL, FILE_APPEND | LOCK_EX);
        return $this->init($returnData);

    }

    public function pay($data)
    {
//        将用户信息储存起来
        $this->memessage = $data;
        $result = $this->setParams($data);
        file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' ."无需密码支付". json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);
//        @1 签名错误
        if($result['result']['errno'] === 'SIGNERROR'){
            return array('flag' => false, 'message' => "支付失败");
//            return array('flag' => false, 'message' => $result['result']['errmsg']);
        }
//        @2 二维码仅限支付一次 或者其他的
        if($result['result']['errno'] === 'AUTH_CODE_INVALID'){
            return array('flag' => false, 'message' => "支付失败");
//            return array('flag' => false, 'message' => substr($result['result']['errmsg'],4));
        }
///      @3 支付成功 而且不用输入密码
        if($result['result']['errno'] === '0'){
            file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' ."result". json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);
            $pay_change = M("pay");
            $remark=$result['terminal_serialno'];
            $data['customer_id'] = $result['openid'];
            if($pay_change->where("remark=$remark")->find())$pay_change->where("remark=$remark")->save($data);
            return array('flag' => true, 'message' => "支付成功");
//            return array('flag' => true, 'message' => $result['result']['errmsg']);
        }
//     @ 支付需要输入密码的
        $this->memessage['orderid']=$this->values['orderid'];

        $queryTimes = 4;
        while ($queryTimes > 0) {
            $succResult = 0;
            $queryResult = $this->query($succResult);
            //如果需要等待5s后继续
            if ($succResult == 3) {
                sleep(5);
                $queryTimes--;
                continue;
            } else if ($succResult == 2) {
                sleep(5);
                $queryTimes--;
                continue;
            } else if ($succResult == 1) {
                //查询成功
//                return array('flag'=>true,'message'=>$queryResult);
                file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' . "密码支付成功".json_encode($queryResult) . PHP_EOL, FILE_APPEND | LOCK_EX);
                $pay_change = M("pay");
                $remark=$queryResult['terminal_serialno'];
                $data['customer_id'] = $queryResult['sub_openid'];
                if($pay_change->where("remark=$remark")->find())$pay_change->where("remark=$remark")->save($data);

                return array('flag'=>true,'message'=>'收款成功');
            }else {
                //订单交易失败
                file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' . "密码支付失败".json_encode($queryResult) . PHP_EOL, FILE_APPEND | LOCK_EX);
                    return array('flag'=>false,'message'=>'收款失败，请重新收款');
            }
        }
        file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' . "时间过长" . PHP_EOL, FILE_APPEND | LOCK_EX);
        return array('flag'=>false,'message'=>'收款失败，请重新收款');

        //④、次确认失败，则撤销订单
        if (!$this->cancel($this->out_trade_no)) {

            return array('flag'=>false,'message'=>'订单交易时间过长，已撤销订单');

//            exit('撤销订单失败!');
        }

        return array('flag'=>false,'message'=>'订单交易时间过长，已撤销订单');

    }

    /**
     *
     * 查询订单情况
     * @param string $out_trade_no 商户订单号
     * @param int $succCode 查询订单结果
     * @return 0 订单不成功，1表示订单成功，2表示继续等待
     */
    private function query(&$succCode)
    {
        $queryOrderInput = new orderQuery($this->memessage);

        //file_put_contents('./data/log/wz/micropay.log', date("Y-m-d H:i:s") . json_decode($this->values) . PHP_EOL, FILE_APPEND | LOCK_EX);
        $result = $queryOrderInput->orderQuery();
        if (!$result) {
            exit('调用订单查询接口失败!');
        }

        //支付成功
        if ($result['result']['errno'] === '0' && $result['payment'] === '1') {
            $succCode = 1;

            return $result;
        }

        //支付中
        if ($result['result']['errno'] === '1' && $result['payment'] === '0') {
            $succCode = 2;

            return false;
        }

        //需要用户输入支付密码
        if ($result['result']['errno'] === 'USERPAYING' && $result['payment'] === '0') {
            $succCode = 3;
            return false;
        }
        return false;
    }

    /**
     *
     * 撤销订单，如果失败会重复调用5次
     * @param string $out_trade_no
     * @param 调用深度 $depth
     */
    private function cancel($out_trade_no, $depth = 0)
    {

        if ($depth > 5) {
            return false;
        }
//        $clostOrder = new WxPayReverse(array('out_trade_no' => $this->out_trade_no, 'amount' => $this->amount));
        $clostOrder = new WxPayReverse($this->memessage);
        $result = $clostOrder->reverse();
        file_put_contents('./data/log/wz/weixin/shuaka.log', date("Y-m-d H:i:s") . '   ' ."订单取消时间". json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);
        if (!$result) {
            exit('调用撤销订单接口失败!');
        }

        //如果结果为success且不需要重新调用撤销，则表示撤销成功
        if ($result['result']['errno'] === '0' && $result['recall'] === 'N') {
            return true;
        } else if ($result['recall'] == 'Y') {
            sleep(3);
            return $this->cancel($out_trade_no, ++$depth);
        }

        return false;
    }



}