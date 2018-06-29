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
        if (!$this->IsSignSet()) {
            exit('签名错误');
        }
        $sign = $this->MakeSign();
        //微众那边返回sign有时候大写有时候小写,等后期修复去掉strtoupper
        if (strtoupper($this->GetSign()) == strtoupper($sign)) {
            return true;
        }
        exit('签名错误!');
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
        ksort($this->values);
        $string = $this->wToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . WzPayConf_pub::ORDER_KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写(微众刷卡支付返回的sign是小写的,后期做修改.如果使用刷卡支付要注释转换大写的代码)
        $result = strtoupper($string);

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
        $this->CheckSign();

        return $this->GetValues();
    }
}

/**
 * Class orderQuery 查询订单
 */
class orderQuery extends WxPayDataBase
{
    //微众银行订单号
    private $orderid;


    public function __construct($orderid)
    {
        $this->orderid = $orderid;
    }

    private function setParams()
    {
        $this->setParameter('merchant_code', WzPayConf_pub::MCHID);
        $this->setParameter('terminal_code', 'web');
        $this->setParameter('orderid', $this->orderid);
        $this->SetSign();
        $queryData = json_encode($this->values);
        $returnData = $this->postXmlSSLCurl($queryData, WzPayConf_pub::NATIVEPAY_QUERYORDER);
//        echo '<hr/>';
//        dump($returnData);
//        echo '<hr/>';

        return $this->init($returnData);
    }
//    public function orderQuery()
//    {
//
//        return $this->setParams();
//    }
}


/**
 * Class nativepay 扫码支付
 */
class nativepay extends WxPayDataBase
{
    //扫码支付的一些参数
    private $parameters;

    private function parameterInit()
    {
        $this->setParameter('merchant_code', WzPayConf_pub::MCHID);
        $this->setParameter('terminal_code', 'web');
        $this->setParameter('terminal_serialno', $this->makeOrderSn());
        $this->setParameter('amount', $this->parameters['amount']);
        $this->setParameter('product', '测试');
        $this->setParameter('notify_url', WzPayConf_pub::NATIVE_CALL_URL);
        $this->SetSign();
        $queryData = json_encode($this->values);
        $returnData = $this->postXmlSSLCurl($queryData, WzPayConf_pub::NATIVEPAY);

        return $this->init($returnData);
    }

    public function pay($data)
    {
        $this->parameters = $data;
        $result = $this->parameterInit();
        if ($result['result']['errno'] !== '0') {
            return array('flag' => false, 'message' => '生成二维码失败!');
        } else {
            return array('flag' => true, 'message' => $result);
        }
    }

    /**
     * 回调
     */
    public function callback()
    {
        // 获取json
        $json_str = file_get_contents('php://input', 'r');
        //验证返回参数
        $returnData = $this->init($json_str);
        if ($returnData['status'] === '0' && $returnData['result_code'] === '0') {
            //通知微众那边支付结果
            echo json_encode(array('return_code' => 'SUCCESS'));

            return $returnData;
        } else {
            echo json_encode(array('return_code' => 'FAIL'));

            return false;
        }

    }
}
