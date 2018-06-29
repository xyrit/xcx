<?php
namespace Wzpay;
use \Wzpay\WzPayConf_pub;
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/1/6
 * Time: 10:55
 */
/**
 *        trimString()，设置参数时需要用到的字符处理函数
 *        createNoncestr()，产生随机字符串，不长于32位
 *        formatBizQueryParaMap(),格式化参数，签名过程需要用到
 *        getSign(),生成签名
 *        arrayToXml(),array转xml
 *        xmlToArray(),xml转 array
 *        postXmlCurl(),以post方式提交xml到对应的接口url
 *        postXmlSSLCurl(),使用证书，以post方式提交xml到对应的接口url
 **/


class Wzpay
{
    //请求参数
    private $parameters;
    //交易加密字符串的key
    private $key;

    public function __construct()
    {

    }

    /**
     *    作用：array转xml
     */
    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     * 将xml转为array
     * @param  string $xml xml字符串
     * @return array       转换得到的数组
     */
    private function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        return $result;
    }

    /**
     * @param $arr 要加密的数组
     * @param $sign 当前使用的key
     * @return string 生成签名
     */
    private function getSign($arr)
    {
        //过滤null和空
        $Parameters = array_filter($arr,function($v){
            if($v === null || $v === ''){
                return false;
            }
            return true;
        });
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
//        echo '【string1】' . $String . '</br>';
        //签名步骤二：在string后加入KEY
        $key = $this->key ? $this->key : WzPayConf_pub::APPLY_KEY;
        $String = $String . "&key=" . $key;
//        echo "【string2】" . $String . "</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
//        echo "【string3】 " . $String . "</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
//        echo "【result】 " . $result_ . "</br>";
        return $result_;
    }


    /**
     *    作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = json_encode($v);
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
     *    作用：产生随机字符串，不长于32位
     */
    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     *    作用：以post方式提交xml到对应的接口url
     */
    private function postXmlCurl($xml, $url, $second = 30)
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
    private function postXmlSSLCurl($xml, $url, $second = 30)
    {
		p($xml);
		p($url);
		
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


    /**
     *    作用：设置请求参数
     */
    public function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$parameter] = $parameterValue;
    }


    /**
     * @param  key请求 1. APPLY_KEY 商户进件 2. ORDER_KEY 交易key
     * @return string
     */
    private function createXml()
    {
        $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
        $this->parameters["sign"] = $this->getSign($this->parameters);//签名

        return $this->arrayToXml($this->parameters);
    }

    /**
     * 验证签名，并回应微信。
     * 对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
     * 微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
     * 尽可能提高通知的成功率，但微信不保证通知最终能成功。
     * 验证返回数据是否一致
     * @return array 返回数组格式的notify数据
     */
    public function notify()
    {
        // 获取json
        $json_str = file_get_contents('php://input', 'r');
        // 转成php数组
        $data = json_decode($json_str,true);
        file_put_contents('./data/log/wz/weixin/weixin.log', date("Y-m-d H:i:s") . '扫码支付回调信息' . $json_str . PHP_EOL, FILE_APPEND | LOCK_EX);
        // 保存原sign
        $data_sign = $data['sign'];
        //获取用户key
        $this->key = $this->getWzKey($data['mch_id']);
        // sign不参与签名
        unset($data['sign']);
        $sign = $this->getSign($data);
        // 判断签名是否正确  判断支付状态
        if ($sign === $data_sign && $data['status'] === '0' && $data['result_code'] === '0') {
                $result = $data;
        } else {
            $result = false;
        }
//        // 返回状态给微信服务器
//        if ($result) {
//            $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
//        } else {
//            $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
//        }
        // 以josn的形式返回状态给微众服务器
        if ($result) {
            $str = json_encode(array('return_code'=>'SUCCESS','return_msg'=>'OK'));
        } else {
            $str = json_encode(array('return_code'=>'FAIL','return_msg'=>'签名失败'));
        }

        echo $str;
        return $result;
    }

    /**
     * @return string 服务器IP
     */
    private function get_server_ip() {
        if (isset($_SERVER)) {
            if($_SERVER['SERVER_ADDR']) {
                $server_ip = $_SERVER['SERVER_ADDR'];
            } else {
                $server_ip = $_SERVER['LOCAL_ADDR'];
            }
        } else {
            $server_ip = getenv('SERVER_ADDR');
        }
        return $server_ip;
    }

    /**
     * 获取jssdk需要用到的数据
     * @return array jssdk需要用到的数据
     */
    public function getParameters()
    {
        $this->setParameter('Service', 'pay.weixin.jspay');
//        $this->setParameter('mch_id', \WzPayConf_pub::MCHID);
        $this->setParameter('device_info', 'web');
        $this->setParameter('sub_appid', WzPayConf_pub::APPID);
        $this->setParameter('mch_create_ip', $this->get_server_ip());
        $this->setParameter('notify_url', WzPayConf_pub::NOTIFY_URL);
        $this->setParameter('callback_url', WzPayConf_pub::CALLBACK_URL);
		
        //获取用户key
        $this->key = $this->getWzKey($this->parameters['mch_id']);
        $xml = $this->createXml();
//        dump($xml);
        $returnData = $this->postXmlSSLCurl($xml, WzPayConf_pub::POST_ORDER);
		p($returnData);
		if($returnData==false){
				return false;
		}
//        dump($returnData);
        $result = $this->xmlToArray($returnData);
//        dump($result);
        // 显示错误信息
        if ($result['status'] !== '0') {
            if($result['message']=="签名不匹配"){
                file_put_contents('./data/log/wz/weixin/weixin.log', date("Y-m-d H:i:s") . '数组' . json_encode($result) . PHP_EOL, FILE_APPEND | LOCK_EX);
                die("系统正在维护,请稍后再试");
            }
            die($result['message']);
        }
		
        return $result['pay_info'];

    }

    /**
     * 订单请求
     * @param $data
     * @return array
     */
    public function queryOrder($data)
    {
        $this->setParameter('Service', 'pay.weixin.jspay');
//        $this->setParameter('mch_id', \WzPayConf_pub::MCHID);
        $this->setParameter('mch_id', $data['mch_id']);
        $this->setParameter('out_trade_no', $data['out_trade_no']);
        $this->setParameter('transaction_id', $data['transaction_id']);
        //获取用户key
        $this->key = $this->getWzKey($data['mch_id']);
        $xml = $this->createXml();
//        dump($xml);
        $returnData = $this->postXmlSSLCurl($xml,WzPayConf_pub::QUERY_ORDER);
        $result = $this->xmlToArray($returnData);
//        dump($result);
        // 显示错误信息
        if ($result['status'] !== '0') {
            die($result['message']);
        }

        return $result;
    }

    public function apply()
    {
        $this->setParameter('merchantName', '深圳前海洋仆淘电子商务有限公司');
        $this->setParameter('merchantAlis', '洋仆淘跨境商城');
        $this->setParameter('merchantArea', '5840');
        $this->setParameter('bankName', '招商银行');
        $this->setParameter('revactBankNo', '308584000013');
        $this->setParameter('bankAccoutName', '深圳前海洋仆淘电子商务有限公司');
        $this->setParameter('bankAccout', '755929903810201');
        $this->setParameter('agency', '1075840001');
        $this->setParameter('servicePhone', '075566607274');
        $this->setParameter('business', '0003');
        $this->setParameter('merchantNature', '私营企业');
        $this->setParameter('wxCostRate', '0.4');
        $this->setParameter('companyFlag', '01');
        $xml = $this->createXml('APPLY_KEY');
        dump($xml);
        $returnData = $this->postXmlSSLCurl($xml, WzPayConf_pub::APPLY_URL);
        echo '<hr/>';
        dump($returnData);
        $xmlObj = simplexml_load_string($returnData);
        dump($xmlObj);
        echo $returnData;

    }

    private function getWzKey($mch_id)
    {
        return db('merchants_cate')->where(array('wx_mchid' => $mch_id))->value('wx_key');
    }

}