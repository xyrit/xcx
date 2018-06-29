<?php
header("Content-Type: text/html; charset=utf-8");
/**
 * 微信支付帮助库
 * ====================================================
 * 接口分三种类型：
 * 【请求型接口】--Wxpay_client_
 *        统一支付接口类--UnifiedOrder
 *        订单查询接口--OrderQuery
 *        退款申请接口--Refund
 *        退款查询接口--RefundQuery
 *        对账单接口--DownloadBill
 *        短链接转换接口--ShortUrl
 * 【响应型接口】--Wxpay_server_
 *        通用通知接口--Notify
 *        Native支付——请求商家获取商品信息接口--NativeCall
 * 【其他】
 *        静态链接二维码--NativeLink
 *        JSAPI支付--JsApi
 * =====================================================
 * 【CommonUtil】常用工具：
 *        trimString()，设置参数时需要用到的字符处理函数
 *        createNoncestr()，产生随机字符串，不长于32位
 *        formatBizQueryParaMap(),格式化参数，签名过程需要用到
 *        getSign(),生成签名
 *        arrayToXml(),array转xml
 *        xmlToArray(),xml转 array
 *        postXmlCurl(),以post方式提交xml到对应的接口url
 *        postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
 */
include_once("SDKRuntimeException.php");
include_once("WxPay.pub.config.php");

/**
 * 所有接口的基类
 */
class Common_util_pub
{
    function __construct()
    {
    }

    function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     *    作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     *    作用：格式化参数，签名过程需要使用
     */
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    /**
     *    作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            if (empty($v) && $v != '0') {
                continue;
            }
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . WxPayConf_pub::KEY;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    /**
     *    作用：array转xml
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
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
     *    作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array        
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     *    作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {

        //初始化curl        
        $ch = curl_init();
        //设置超时
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
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //curl_close($ch);
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

    /**
     *    作用：使用证书，以post方式提交xml到对应的接口url
     */
    function postXmlSSLCurl($xml, $url, $second = 30)
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
        curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__) . WxPayConf_pub::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__) . WxPayConf_pub::SSLKEY_PATH);
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
            echo "curl出错，错误码:$error";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     *    作用：打印数组
     */
    function printErr($wording = '', $err = '')
    {
        print_r('<pre>');
        echo $wording . "</br>";
        var_dump($err);
        print_r('</pre>');
    }
}

/**
 * 请求型接口的基类
 */
class Wxpay_client_pub extends Common_util_pub
{
    var $parameters;//请求参数，类型为关联数组
    public $response;//微信返回的响应
    public $result;//返回参数，类型为关联数组
    var $url;//接口链接
    var $curl_timeout;//curl超时时间

    /**
     *    作用：设置请求参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     *    作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml()
    {
        $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
        $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
        $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters);//签名
        return $this->arrayToXml($this->parameters);
    }

    /**
     *    作用：post请求xml
     */
    function postXml()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    /**
     *    作用：使用证书post请求xml
     */
    function postXmlSSL()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlSSLCurl($xml, $this->url, $this->curl_timeout);
        return $this->response;
    }

    /**
     *    作用：获取结果，默认不使用证书
     */
    function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

    /**
     *    作用：获取结果，默认不使用证书
     */
    function getBillResult()
    {
        $this->postXml();
        return $this->response;

    }


}


/**
 * 统一支付接口类
 */
class UnifiedOrder_pub extends Wxpay_client_pub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            //检测必填参数
            if ($this->parameters["out_trade_no"] == null) {
                throw new SDKRuntimeException("缺少统一支付接口必填参数out_trade_no！" . "<br>");
            } elseif ($this->parameters["body"] == null) {
                throw new SDKRuntimeException("缺少统一支付接口必填参数body！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new SDKRuntimeException("缺少统一支付接口必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["notify_url"] == null) {
                throw new SDKRuntimeException("缺少统一支付接口必填参数notify_url！" . "<br>");
            } elseif ($this->parameters["trade_type"] == null) {
                throw new SDKRuntimeException("缺少统一支付接口必填参数trade_type！" . "<br>");
            }
            /*elseif ($this->parameters["trade_type"] == "JSAPI" &&
                $this->parameters["openid"] == NULL){
                throw new SDKRuntimeException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
            }*/
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号

            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
            //$xml=$this->createXml($new);
            //return $xml;
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     * 获取prepay_id
     */
    function getPrepayId()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        $prepay_id = $this->result["prepay_id"];
        return $prepay_id;
    }
}

/**
 * 订单查询接口
 */
class OrderQuery_pub extends Wxpay_client_pub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/orderquery";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            //检测必填参数
            if ($this->parameters["out_trade_no"] == null &&
                $this->parameters["transaction_id"] == null
            ) {
                throw new SDKRuntimeException("订单查询接口中，out_trade_no、transaction_id至少填一个！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

}

/**
 * 退款申请接口
 */
class Refund_pub extends Wxpay_client_pub
{

    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    public function payBack()
    {
        $this->url = WxPayConf_pub::PAY_BACK_URL;
        $this->curl_timeout = 5;
        return $this->getResult();
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            //检测必填参数
            if ($this->parameters["out_trade_no"] == null && $this->parameters["transaction_id"] == null) {
                throw new SDKRuntimeException("退款申请接口中，out_trade_no、transaction_id至少填一个！" . "<br>");
            } elseif ($this->parameters["out_refund_no"] == null) {
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数out_refund_no！" . "<br>");
            } elseif ($this->parameters["total_fee"] == null) {
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数total_fee！" . "<br>");
            } elseif ($this->parameters["refund_fee"] == null) {
                throw new SDKRuntimeException("退款申请接口中，缺少必填参数refund_fee！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     *    作用：获取结果，使用证书通信
     */
    function getResult()
    {
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        file_put_contents('./data/log/weixin/' . date("Y_m_") . 'pay_back.log', date("Y-m-d H:i:s") . 'getResult返回数据：' .json_encode($this->result). PHP_EOL, FILE_APPEND | LOCK_EX);
        return $this->result;
    }

}


/**
 * 退款查询接口
 */
class RefundQuery_pub extends Wxpay_client_pub
{

    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/refundquery";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            if ($this->parameters["out_refund_no"] == null &&
                $this->parameters["out_trade_no"] == null &&
                $this->parameters["transaction_id"] == null &&
                $this->parameters["refund_id "] == null
            ) {
                throw new SDKRuntimeException("退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     *    作用：获取结果，使用证书通信
     */
    function getResult()
    {
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

}

/**
 * 对账单接口
 */
class DownloadBill_pub extends Wxpay_client_pub
{

    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/pay/downloadbill";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            if ($this->parameters["bill_date"] == null) {
                throw new SDKRuntimeException("对账单接口中，缺少必填参数bill_date！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     *    作用：获取结果，默认不使用证书
     */
    function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->result_xml);
        return $this->result;
    }


}

/**
 * 短链接转换接口
 */
class ShortUrl_pub extends Wxpay_client_pub
{
    function __construct()
    {
        //设置接口链接
        $this->url = "https://api.mch.weixin.qq.com/tools/shorturl";
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        try {
            if ($this->parameters["long_url"] == null) {
                throw new SDKRuntimeException("短链接转换接口中，缺少必填参数long_url！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return $this->arrayToXml($this->parameters);
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     * 获取prepay_id
     */
    function getShortUrl()
    {
        $this->postXml();
        $prepay_id = $this->result["short_url"];
        return $prepay_id;
    }

}

/**
 * 响应型接口基类
 */
class Wxpay_server_pub extends Common_util_pub
{
    public $data;//接收到的数据，类型为关联数组
    var $returnParameters;//返回参数，类型为关联数组

    /**
     * 将微信的请求xml转换成关联数组，以方便数据处理
     */
    function saveData($xml)
    {
        $this->data = $this->xmlToArray($xml);
    }

    function checkSign()
    {
        $tmpData = $this->data;
        unset($tmpData['sign']);
        $sign = $this->getSign($tmpData);//本地签名
        if ($this->data['sign'] == $sign) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 获取微信的请求数据
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * 设置返回微信的xml数据
     */
    function setReturnParameter($parameter, $parameterValue)
    {
        $this->returnParameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        return $this->arrayToXml($this->returnParameters);
    }

    function returnNotifyXml($arr)
    {
        return $this->arrayToXml($arr);
    }

    /**
     * 将xml数据返回微信
     */
    function returnXml()
    {
        $returnXml = $this->createXml();
        return $returnXml;
    }
}


/**
 * 通用通知接口
 */
class Notify_pub extends Wxpay_server_pub
{

}


/**
 * 请求商家获取商品信息接口
 */
class NativeCall_pub extends Wxpay_server_pub
{
    /**
     * 生成接口参数xml
     */
    function createXml()
    {
        if ($this->returnParameters["return_code"] == "SUCCESS") {
            $this->returnParameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->returnParameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $this->returnParameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->returnParameters["sign"] = $this->getSign($this->returnParameters);//签名
        }
        return $this->arrayToXml($this->returnParameters);
    }

    /**
     * 获取product_id
     */
    function getProductId()
    {
        $product_id = $this->data["product_id"];
        return $product_id;
    }

}

/**
 * 静态链接二维码
 */
class NativeLink_pub extends Common_util_pub
{
    var $parameters;//静态链接参数
    var $url;//静态链接

    function __construct()
    {
    }

    /**
     * 设置参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 生成Native支付链接二维码
     */
    function createLink()
    {
        try {
            if ($this->parameters["product_id"] == null) {
                throw new SDKRuntimeException("缺少Native支付二维码链接必填参数product_id！" . "<br>");
            }
            $this->parameters["appid"] = WxPayConf_pub::APPID;//公众账号ID
            $this->parameters["mch_id"] = WxPayConf_pub::MCHID;//商户号
            $time_stamp = time();
            $this->parameters["time_stamp"] = "$time_stamp";//时间戳
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            $bizString = $this->formatBizQueryParaMap($this->parameters, false);
            $this->url = "weixin://wxpay/bizpayurl?" . $bizString;
        } catch (SDKRuntimeException $e) {
            die($e->errorMessage());
        }
    }

    /**
     * 返回链接
     */
    function getUrl()
    {
        $this->createLink();
        return $this->url;
    }
}

/**
 * JSAPI支付——H5网页端调起支付接口
 */
class JsApi_pub extends Common_util_pub
{
    var $code;//code码，用以获取openid
    var $openid;//用户的openid
    var $parameters;//jsapi参数，格式为json
    var $prepay_id;//使用统一支付接口得到的预支付id
    var $curl_timeout;//curl超时时间

    function __construct()
    {
        //设置curl超时时间
        $this->curl_timeout = WxPayConf_pub::CURL_TIMEOUT;
    }

    /**
     *    作用：生成可以获得code的url
     */
    function createOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = WxPayConf_pub::SUB_APPID;
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }

    /**
     *    作用：生成可以获得openid的url
     */
    function createOauthUrlForOpenid()
    {
        $urlObj["appid"] = WxPayConf_pub::SUB_APPID;
        $urlObj["secret"] = WxPayConf_pub::SUB_APPSECRET;
        $urlObj["code"] = $this->code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }


    /**
     *    作用：通过curl向微信提交code，以获取openid
     */
    function getOpenid()
    {
        $url = $this->createOauthUrlForOpenid();
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res, true);
        $this->openid = $data['openid'];
        return $this->openid;
    }

    /**
     *    作用：设置prepay_id
     */
    function setPrepayId($prepayId)
    {
        $this->prepay_id = $prepayId;
    }

    /**
     *    作用：设置code
     */
    function setCode($code_)
    {
        $this->code = $code_;
    }

    /**
     *    作用：设置jsapi的参数
     */
    public function getParameters()
    {
        $jsApiObj["appId"] = WxPayConf_pub::APPID;
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = $this->createNoncestr();
        $jsApiObj["package"] = "prepay_id=$this->prepay_id";
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = $this->getSign($jsApiObj);
        $this->parameters = json_encode($jsApiObj);

        return $this->parameters;
    }
}

/**
 *
 * 刷卡支付实现类
 * 该类实现了一个刷卡支付的流程，流程如下：
 * 1、提交刷卡支付
 * 2、根据返回结果决定是否需要查询订单，如果查询之后订单还未变则需要返回查询（一般反复查10次）
 * 3、如果反复查询10订单依然不变，则发起撤销订单
 * 4、撤销订单需要循环撤销，一直撤销成功为止（注意循环次数，建议10次）
 *
 * 该类是微信支付提供的样例程序，商户可根据自己的需求修改，或者使用lib中的api自行开发，为了防止
 * 查询时hold住后台php进程，商户查询和撤销逻辑可在前端调用
 *
 * @author widy
 *
 */
class WxPayMicroPay extends Wxpay_client_pub
{
    /**
     *
     * 提交刷卡支付，并且确认结果，接口比较慢
     * @throws WxpayException
     * @return 返回接口的结果
     */
    public function pay()
    {
        $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
        $this->url = WxPayConf_pub::MICROPAY_URL;
        $this->curl_timeout = 5;
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        file_put_contents('./data/log/weixin/' . date("Y_m_") . 'micro.log', date("Y-m-d H:i:s") . '返回参数:' . json_encode($this->result) . PHP_EOL, FILE_APPEND | LOCK_EX);
        $result = $this->result;
        //签名验证
        $sign = $result['sign'];
        unset($result['sign']);
        $_sign = $this->getSign($result);
        if ($sign != $_sign) {
            return array('flag' => false, 'msg' => "签名错误！");
        }
        //如果返回成功
        /*if (!array_key_exists("return_code", $result)
            || !array_key_exists("out_trade_no", $result)
            || !array_key_exists("result_code", $result)
        ) {
            return array('flag' => false, 'msg' => "接口调用失败,请确认是否输入是否有误！");
//            throw new WxPayException("接口调用失败！");
        }
        $out_trade_no = $this->parameters['out_trade_no'];

        //②、接口调用成功，明确返回调用失败
        if ($result["return_code"] == "SUCCESS" &&
            $result["result_code"] == "FAIL" &&
            $result["err_code"] != "USERPAYING" &&
            $result["err_code"] != "SYSTEMERROR"
        ) {
            return array('flag' => false, 'msg' => $result["err_code"]);
        }*/
        $out_trade_no = $this->parameters['out_trade_no'];
        $pay_change = M("pay");
        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            $data['wx_remark'] = $result['transaction_id'];
            $data['customer_id'] = $result['openid'];
            $data['status'] = 1;
            if ($pay_change->where("remark=$out_trade_no")->find()) {
                $pay_change->where("remark=$out_trade_no")->save($data);
            }
            return array('flag' => true, 'message' => "支付成功");
        } elseif ($result["return_code"] == "SUCCESS" && $result["result_code"] == "FAIL") {
            //③、确认支付是否成功
            $queryTimes = 10;
            while ($queryTimes > 0) {
                $queryTimes--;
                $succResult = 0;
                sleep(2);
                $queryResult = $this->query($out_trade_no, $succResult);
                //如果需要等待1s后继续
                if ($succResult == 2) {
                    continue;
                } else if ($queryResult == 1) {//查询成功
                    $data['customer_id'] = $queryResult['openid'];
                    $data['status'] = 1;
                    if ($pay_change->where("remark=$out_trade_no")->find()) {
                        $pay_change->where("remark=$out_trade_no")->save($data);
                    }
                    return array('flag' => true, 'msg' => "支付成功");
                } else {//订单交易失败
                    return array('flag' => false, 'msg' => "订单交易失败");
                }
            }
        } else {
            return array('flag' => false, 'msg' => "接口调用失败,请确认是否输入是否有误！");
        }


        //④、次确认失败，则撤销订单
        if (!$this->cancel($out_trade_no)) {
            return array('flag' => false, 'message' => '订单交易时间过长，已撤销订单');

//            throw new WxpayException("撤销单失败！");
        }
        return array('flag' => false, 'message' => '订单交易时间过长，已撤销订单');
    }


    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     *
     * 查询订单情况
     * @param string $out_trade_no 商户订单号
     * @param int $succCode 查询订单结果
     * @return 0 订单不成功，1表示订单成功，2表示继续等待
     */
    public function query($out_trade_no, &$succCode)
    {
        $this->parameters = null;
        $this->parameters["out_trade_no"] = $out_trade_no;
        if(!$this->sub_mch_id){
            $this->sub_mch_id = M('pay p')->join('ypt_merchants_upwx wx on wx.mid=p.merchant_id', 'left')->where("p.remark='$out_trade_no'")->getField('sub_mchid');
        }
        $this->parameters["sub_mch_id"] = $this->sub_mch_id;
        $this->url = WxPayConf_pub::ORDER_QUERY_URL;

        $this->curl_timeout = 5;
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        $result = $this->result;
        file_put_contents('./data/log/weixin/' . date("Y_m_") . 'query.log', date("Y-m-d H:i:s") . '订单号:' . $out_trade_no . '，返回参数:' . json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            //支付成功
            if ($result["trade_state"] == "SUCCESS") {
                $succCode = 1;
            } //用户支付中
            else if ($result["trade_state"] == "USERPAYING") {
                $succCode = 2;
            }
            return $succCode;
        }

        //如果返回错误码为“此交易订单号不存在”则直接认定失败
        if ($result["err_code"] == "ORDERNOTEXIST") {
            $succCode = 0;
        } else {
            //如果是系统错误，则后续继续
            $succCode = 2;
        }
        return false;
    }

    /**
     *
     * 撤销订单，如果失败会重复调用10次
     * @param string $out_trade_no
     * @param 调用深度 $depth
     * @return bool
     */
    public function cancel($out_trade_no, $depth = 0)
    {
        $this->parameters = null;
        if ($depth > 5) {

            return false;
        }
        $this->parameters["out_trade_no"] = $out_trade_no;
        $this->parameters["sub_mch_id"] = $this->sub_mch_id;
        $this->url = WxPayConf_pub::REVERSE_URL;

        $this->curl_timeout = 5;
        $this->postXmlSSL();
        $this->result = $this->xmlToArray($this->response);
        $result = $this->result;
        file_put_contents('./data/log/weixin/' . date("Y_m_") . 'cancel.log', date("Y-m-d H:i:s") . '订单号:' . $out_trade_no . '，返回参数:' . json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);
        //接口调用失败
        if ($result["return_code"] != "SUCCESS") {
            return false;
        }

        //如果结果为success且不需要重新调用撤销，则表示撤销成功
        if ($result["result_code"] != "SUCCESS"
            && $result["recall"] == "N"
        ) {
            return true;
        } else if ($result["recall"] == "Y") {
            return $this->cancel($out_trade_no, ++$depth);
        }
        return false;
    }
}

?>
