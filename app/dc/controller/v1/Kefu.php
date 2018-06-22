<?php
namespace app\dc\controller\v1;
use think\Controller;
use think\Db;
header('Content-type:text');
define("TOKEN", "youngport");
class Kefu extends Controller
{
    public function index()
    {
           echo 111;
        //youngport
    	//kxNR5GjGDa9iyd6FbvEMrlEabV9liWnBRXzUUSlloIW
    }

    public function check_server(){     //校验服务器地址URL
        if (isset($_GET['echostr'])) {
            $this->valid();
        }else{
            $this->responseMsg();
        }
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }else{
            echo $echoStr.'+++'.TOKEN;
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
    
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
    
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    
        if (!empty($postStr) && is_string($postStr)){
            //禁止引用外部xml实体
            //libxml_disable_entity_loader(true);
            file_put_contents('./data/log/kefu.log', date("Y-m-d H:i:s") . $postStr . PHP_EOL, FILE_APPEND | LOCK_EX);
            //$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $postArr = json_decode($postStr,true);
            if(!empty($postArr['MsgType']) && $postArr['MsgType'] == 'text'){   //文本消息
                $fromUsername = $postArr['FromUserName'];   //发送者openid
                $toUserName = $postArr['ToUserName'];       //小程序id
                $textTpl = array(
                    "ToUserName"=>$fromUsername,
                    "FromUserName"=>$toUserName,
                    "CreateTime"=>time(),
                    "MsgType"=>"transfer_customer_service",
                );
                exit(json_encode($textTpl));
            }elseif(!empty($postArr['MsgType']) && $postArr['MsgType'] == 'image'){ //图文消息
                $fromUsername = $postArr['FromUserName'];   //发送者openid
                $toUserName = $postArr['ToUserName'];       //小程序id
                $textTpl = array(
                    "ToUserName"=>$fromUsername,
                    "FromUserName"=>$toUserName,
                    "CreateTime"=>time(),
                    "MsgType"=>"transfer_customer_service",
                );
                exit(json_encode($textTpl));
            }elseif($postArr['MsgType'] == 'event' && $postArr['Event']=='user_enter_tempsession'){ //进入客服动作
                $fromUsername = $postArr['FromUserName'];   //发送者openid
                $content = '您好，有什么能帮助你?';
                $data=array(
                    "touser"=>$fromUsername,
                    "msgtype"=>"text",
                    "text"=>array("content"=>$content)
                );
                $json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+
                
                $access_token = $this->get_accessToken();
                /* 
                 * POST发送https请求客服接口api
                 */
                $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
                file_put_contents('./data/log/kefu_url.log', date("Y-m-d H:i:s") . $url . PHP_EOL, FILE_APPEND | LOCK_EX);
                //以'json'格式发送post的https请求
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
                if (!empty($json)){
                    curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
                }
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
                $output = curl_exec($curl);
                if (curl_errno($curl)) {
                    echo 'Errno'.curl_error($curl);//捕抓异常
                    file_put_contents('./data/log/kefu_err.log', date("Y-m-d H:i:s") . 'Errno'.curl_error($curl) . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                curl_close($curl);
                if($output == 0){
                    echo 'success';exit;
                    file_put_contents('./data/log/kefu_succ.log', date("Y-m-d H:i:s") . 'success' . PHP_EOL, FILE_APPEND | LOCK_EX);
                }
                
            }else{
                exit('aaa');
            }
        }else{
            echo "";
            exit;
        }
    }
    /* 调用微信api，获取access_token，有效期7200s -xzz0704 */
    public function get_accessToken(){
        /* 在有效期，直接返回access_token */
        if(S('access_token')){
            return S('access_token');
        } else {
        	/* 不在有效期，重新发送请求，获取access_token */
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3fa82ee7deaa4a21&secret=6b6a7b6994c220b5d2484e7735c0605a';
            $result = curl_get_https($url);
            $res = json_decode($result,true);   //json字符串转数组
    
            if($res){
                S('access_token',$res['access_token'],7100);
                return S('access_token');
            }else{
                return 'api return error';
            }
        }
    }

    /**
	 * 对密文进行解密
	 * @param string $encrypted 需要解密的密文
	 * @return string 解密得到的明文
	 */
	public function decrypt($encrypted, $appid)
	{

		try {
			//使用BASE64对需要解密的字符串进行解码
			$ciphertext_dec = base64_decode($encrypted);
			$module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
			$iv = substr($this->key, 0, 16);
			mcrypt_generic_init($module, $this->key, $iv);

			//解密
			$decrypted = mdecrypt_generic($module, $ciphertext_dec);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
		} catch (Exception $e) {
			return array(ErrorCode::$DecryptAESError, null);
		}


		try {
			//去除补位字符
			$pkc_encoder = new PKCS7Encoder;
			$result = $pkc_encoder->decode($decrypted);
			//去除16位随机字符串,网络字节序和AppId
			if (strlen($result) < 16)
				return "";
			$content = substr($result, 16, strlen($result));
			$len_list = unpack("N", substr($content, 0, 4));
			$xml_len = $len_list[1];
			$xml_content = substr($content, 4, $xml_len);
			$from_appid = substr($content, $xml_len + 4);
		} catch (Exception $e) {
			//print $e;
			return array(ErrorCode::$IllegalBuffer, null);
		}
		if ($from_appid != $appid)
			return array(ErrorCode::$ValidateAppidError, null);
		return array(0, $xml_content);

	}
}
