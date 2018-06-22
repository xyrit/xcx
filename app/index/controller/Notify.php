<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\Message_dc;
class Notify extends controller{

    	//微信
    	public function wx(){
    		// file_put_contents('wx4.txt','这里了');
    				include('./extend/WxPayPubHelper/WxPayPubHelper.php');
    				spl_autoload_unregister('think\\Loader::autoload');
					$unifiedOrder = new \UnifiedOrder_pub();
					spl_autoload_register('think\\Loader::autoload');
					//存储微信的回调
      				$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
      				add_log($xml);
      				//$xml = db('log')->where(array('id'=>41))->value('param');
      				$notify = new \Notify_pub();
      				$notify->saveData($xml);
      				if ($notify->checkSign() == FALSE) {
      					// file_put_contents('wx2.txt','这里了');
				            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
				            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
				    } else {
				    	// file_put_contents('wx3.txt','这里了');
				            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
				            if ($notify->data["return_code"] == "FAIL"){
				             	
				            }elseif($notify->data["result_code"] == "FAIL"){
				            	   
				            }else{
					                //此处应该更新一下订单状态，商户自行增删操作,//在这里操作订单表
					                //log_result($log_name,"【支付成功】:\n".$xml."\n");
					                file_put_contents('wx.txt',$notify->data["out_trade_no"]);
					                $out_trade_no = $notify->data["out_trade_no"];//回调的订单号
					                $transaction_id = $notify->data["transaction_id"];//微信支付订单号
							      	$this->common_zf($out_trade_no,$transaction_id,$notify->data['total_fee']/100,3);
							        $returnXml = $notify->returnXml();
         				   	}
				            
				    }
    	} 

    	//宿州微信
    	public function szwx(){
    		// file_put_contents('wx4.txt','这里了');
    				include('./extend/SzWxPayPubHelper/WxPayPubHelper.php');
    				spl_autoload_unregister('think\\Loader::autoload');
					$unifiedOrder = new \UnifiedOrder_pub();
					spl_autoload_register('think\\Loader::autoload');
					//存储微信的回调
      				$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
      				add_log($xml);
      				//$xml = db('log')->where(array('id'=>41))->value('param');
      				$notify = new \Notify_pub();
      				$notify->saveData($xml);
      				if ($notify->checkSign() == FALSE) {
      					// file_put_contents('wx2.txt','这里了');
				            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
				            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
				    } else {
				    	// file_put_contents('wx3.txt','这里了');
				            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
				            if ($notify->data["return_code"] == "FAIL"){
				             	
				            }elseif($notify->data["result_code"] == "FAIL"){
				            	   
				            }else{
					                //此处应该更新一下订单状态，商户自行增删操作,//在这里操作订单表
					                //log_result($log_name,"【支付成功】:\n".$xml."\n");
					                file_put_contents('wx.txt',$notify->data["out_trade_no"]);
					                $out_trade_no = $notify->data["out_trade_no"];//回调的订单号
					                $transaction_id = $notify->data["transaction_id"];//微信支付订单号
							      	$this->common_zf($out_trade_no,$transaction_id,$notify->data['total_fee']/100,9);
							        $returnXml = $notify->returnXml();
         				   	}
				            
				    }
    	} 
    	//4","out_trade_no":"2018041203575736971","out_transaction_id":"4200000077201804127276548388","pay_result":"0","result_code":"0","sign":"241B957EBF6C503127BC16C21A978337","sign_type":"MD5","status":"0","sub_appid":"wx7aa4b28fb4fae496","sub_is_subscribe":"N","sub_openid":"opBrr0LltF_jM4F1NmiuBieV_BG0","time_end":"20180412155812","total_fee":"100","trade_type":"pay.weixin.jspay","transaction_id":"102515302767201804125277888900","version":"2.0"}
    	public function common_zf1(){
    		$this->common_zf('2018041203575736971','102515302767201804125277888900','1',10);
    	}
    	public function wx1(){
    				include('./extend/Wxpay/appWxPayPubHelper/WxPayPubHelper.php');
    				spl_autoload_unregister('think\\Loader::autoload');
					$unifiedOrder = new \UnifiedOrder_pub();
					spl_autoload_register('think\\Loader::autoload');
					//存储微信的回调
      				$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
      				add_log($xml);
      				//$xml = db('log')->where(array('id'=>41))->value('param');
      				$notify = new \Notify_pub();
      				$notify->saveData($xml);
      				if ($notify->checkSign() == FALSE) {
				            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
				            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
				    } else {
				            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
				            if ($notify->data["return_code"] == "FAIL"){
				             	
				            }elseif($notify->data["result_code"] == "FAIL"){
				            	   
				            }else{
					                //此处应该更新一下订单状态，商户自行增删操作,//在这里操作订单表
					                //log_result($log_name,"【支付成功】:\n".$xml."\n");
					                $out_trade_no = $notify->data["out_trade_no"];//回调的订单号
					                $transaction_id = $notify->data["transaction_id"];//微信支付订单号
							      	$this->common_zf($out_trade_no,$transaction_id,$notify->data['total_fee']/100);
							        $returnXml = $notify->returnXml();
         				   	}
				            
				    }
    	} 
    	public function test(){
    			
    				//model('Wx')->moban('fahuo',1,1);
    			model('ScreenMemcardUse')->updateuser('585565955470',10,'赠送');
    	}
    	
    	public function tk(){
    			$data['action']='wallet/trans/refund';
		        $data['version']='2.0';
		        $data['reqTime']= date("YmdHis");
		        $data['orderId']='2017062009431397608';
		        $data['refundOrderId']='date("YmdHis").rand(1000,9999)';
		        $data['reqId']=date("YmdHis").rand(1000,9999);
		        $data['deviceId']='payuser';//终端号
		        $data['totalAmount']=902;
		        $data['operatorId']="POS 操作员";
		        $data['custId']='170615162016687';
		       // $data['orgReqId']=$orgTransId;
		        $data['orgTransId']='706200943116851237';
		        $data=json_encode($data);
		        $data="[".$data."]";
		          $private_key= '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQCbexvFt/rOGUOVDPbT99wWt3ChnmcqRc+lmJkEDHP98c8rd+Ih
V34VfjeA2+bhaJ66ZlN+sxJG871GIA6X9o7MOFjFsdAkXYAK+EyHiRZx4drhoaiM
LqxP+ygH3BlvvEEHUUT+ZW0lg2wgcRrzcUDHKZ0u112cQkZgo+Skivm6QQIDAQAB
AoGAS2g8wvsE9/pGzb5Y49sdciMLzEbQEC+FkvHcnJsRkoM5kAJ3uOX/L5tkfemp
I3+jJBJGwndFEQZbsOwRR+B7xoywgJ5+dlyneXEoNfbOJ4J3tP/IVoIDHr2ax8uW
3/IizcgcL8Wc6AyryaQfFb9nEBMUdTt3k3VUEZC4Ef/xccECQQDJ0dj5e3vYbS7F
yIsNlv5HBVzSK++qbxmefT0ZTrvgYPp/g+tFhY8blzOxhbJj3Cp+FxPqL9GOLg1P
hVNMYYj5AkEAxTian96ke9hQY5FjJ/e6q1fe8KzQG79/aC4q4j7rS5Z35kSuDA/Y
Pko47ta2AI5otCdQVXsvNBhFHaO3FKMViQJBAJcNK+NWS9Qpq9c2iPTL7VcEqXtY
jRG4A6m+vKsjZbTDgNlNyBqJoxmYaoVUtrbNAzTKWwptbd+HkkjRVg4V9ikCQQCX
KFkqqwQ6f4KtraLn4TFLXh/bKzid69oEyU3I9hx1ZLAk5wLW79X3d//G3v3D02Jg
obkqqy10qh1fKDmMMaqxAkB+h+DHSA3k4AmRtuKA+fQ9PoLRSbGqYiKEmGLaZvuE
WBDdsn6coSK8qlh4Jxv9dquCaymS9Y+lGzBh2o4n0jOF
-----END RSA PRIVATE KEY-----';
		        $res=$this->rsaSign($data,$private_key);
		    	$result=$this->httpRequst('https://aop.koolyun.com:443/apmp/rest/v2',$data,$res,'YPT17001P');
		      	$result=json_decode($result,true);
    	}
    	public function rsaSign($data, $private_key){
			 $res=openssl_get_privatekey($private_key);
		    if($res)
		    {
		        openssl_sign($data, $sign,$res);
		    }
		    else {
		        echo "您的私钥格式不正确!"."<br/>"."The format of your private_key is incorrect!";
		        exit();
		    }
		    openssl_free_key($res);
		    $sign=strtoupper(bin2hex($sign));
		    return $sign;
		}
		
		//http 请求
		public function httpRequst($url,$data,$res,$appkey){
			$post_data='params='.$data;
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL, $url);  
			curl_setopt($ch, CURLOPT_POST, 1);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);  
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);  
			curl_setopt($ch, CURLOPT_ENCODING, ""); 
			curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			"Content-Type:application/x-www-form-Urlencoded;charset=utf-8",
			"Accept-Language:zh-cn",
			"x-apsignature:".$res,
			"x-appkey:".$appkey
			));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
			curl_setopt($ch, CURLOPT_TIMEOUT,180);
		
			$header=array(
			"Content-Type:application/x-www-form-Urlencoded;charset=utf-8",
			"Accept-Language:zh-cn",
			"x-apsignature:".$res,
			"x-appkey:".$appkey
			);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);  
			$output = curl_exec($ch);  
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$response_header = substr($output, 0, $header_size);
		    $response_body = substr($output, $header_size);
		    curl_close($ch);
		    $response_body = trim($response_body, '[');
			$response_body = trim($response_body, ']');
		
			$response_body = json_decode($response_body, 1);
		
			$response_header_arr = array();
			$response_header_arr = explode(': ', $response_header);
			if ((json_last_error() != JSON_ERROR_NONE) or empty($response_header_arr))
			{
				throw new QrcodePayException("Analyze return json error.");
			}
			$response_header_return = array();
			if (!empty($response_header_arr[4]))
			{
				$response_header_return['x_apsignature'] = str_replace(array("\r\n", "\r", "\n", "Content-Type"), "", $response_header_arr[4]);
			}
			return json_encode(array('header' => $response_header_return, 'body' => $response_body));
		}
    	public function test12(){
    			$wx = model('Wx');
    			if($wx->moban('pay',2553)){
    				
    			}else{
    				succ($wx->getError());
    			}
    			
    	}
    	public function  common_zf($out_trade_no,$transaction_id,$price,$bank_id){
    				$order = model('order');
    				if($order_id = $order->pay($out_trade_no,$transaction_id,$price,$bank_id)){
    					// file_put_contents('tui.txt','到了吗');
    						model('wx')->moban('pay',$order_id);
    						$message = new Message_dc;
				    		$message->push_order_message($order_id);
				    		$pay = db::name('pay')->where('order_id',$order_id)->find();
    						curl_post('http://sy.youngport.com.cn/index.php?s=Api/Cloud/printer',array('remark'=>$pay['remark']));
    				}
    				//更新订单状态
    	}

    	public function ms1($id){
    			$post = db('log')->where('id',$id)->value('post');
    			$post = json_decode($post,true);
    			$data = json_decode($post['body'],true);
    			if(substr($data['reqId'],-3,3)=='251'){
    					$this->common_zf($data['orderId'],$data['transId'],$data['totalAmount']/100);
    			}
    	}
    	public function xy(){
    			//add_log(file_get_contents('php://input', 'r'));
				add_log(json_encode($this->xmlToArray(file_get_contents('php://input', 'r'))));
				$data = $this->xmlToArray(file_get_contents('php://input', 'r'));
				$this->common_zf($data['out_trade_no'],$data['transaction_id'],$data['total_fee']/100,7);
    	}
    	public function pf(){
    			//add_log(file_get_contents('php://input', 'r'));
				add_log(json_encode($this->xmlToArray(file_get_contents('php://input', 'r'))));
				$data = $this->xmlToArray(file_get_contents('php://input', 'r'));
				$this->common_zf($data['out_trade_no'],$data['transaction_id'],$data['total_fee']/100,10);
    	}
    	public function ms(){
    			add_log();
    			//开始验证签名
    			//$post = db('log')->where('id',347)->value('post');
    			//$post = json_decode($post,true);
    			//p($post);
    			$post = input('post.');
    			//add_log($post);
    			$data = json_decode($post['body'],true);
    			//暂时这样代表验证成功
    			if(substr($data['reqId'],-3,3)=='251'){
    				$this->common_zf($data['orderId'],$data['transId'],$data['totalAmount']/100,2);
    			}
    	}
    	public function zs(){
    		
  				add_log(file_get_contents('php://input', 'r'));
				add_log(json_encode($this->xmlToArray(file_get_contents('php://input', 'r'))));
				$data = $this->xmlToArray(file_get_contents('php://input', 'r'));
//				$param = db::name('log')->where('id',38794)->value('param');
//  			$data = $this->xmlToArray($param);
//				
				$this->common_zf($data['out_trade_no'],$data['transaction_id'],$data['cash_fee']/100,4);
    			
    	}
    	private function xmlToArray($xml){   
	        //禁止引用外部xml实体
	        libxml_disable_entity_loader(true);
	        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
	        return $values;
  	    }
    	public function fahuo(){
    			$sign = input('sign');
    			$type = input('type','fahuo');
    			if($sign!=='tiancai'){
    					err('sign is wrong');
    			}
    			($order_id = input('order_id')) || err('order_id is empty');
    			model('wx')->moban($type,$order_id);
    	}   
    	public function wqq(){
    			//延签
    			//{\"transId\":705270951090950830,\"orderId\":\"2017052606295654456\",\"reqId\":\"201705270946542195\",\"totalAmount\":\"1\",\"transAmount\":\"1\",\"transTime\":\"20170527101653\",\"currency\":\"CNY\",\"transType\":\"1\",\"transResult\":\"2\",\"acquirerType\":\"wechat\",\"walletTransId\":\"4003892001201705272803363157\",\"walletOrderId\":\"1705270000005289\",\"uuid\":\"o4GgauAp_T91qmQE4jBaiZn1Y-tQ\",\"custId\":\"170525183627874\"}
    	}
    	public function create_store(){
    				$url = 'http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token=OCxIliH6eGcF9wUkET2qoeqs-U5h534OMR5dTu3f8x6G8KcXcNKedImnqF-XOeVFec2DM52t_z-cO1vWWhqZFl8aNjtfKc1gPRJa_or_YXoXZWjAFAMOL';
    			
    			$params ='{"business":
    			{"base_info":{
                   "business_name":"测试啊",
                   "branch_name":"不超过10个字",
                   "province":"广东省",
                   "city":"深圳市",
                   "district":"宝安区",
                   "address":"门店所在的详细街道地址",
                   "telephone":"18823404165",
                   "categories":["美食"], 
                   "offset_type":1,
                   "longitude":"115.32375",
                   "latitude":"25.097486"
             }
    }
}';		
		
				$p['buffer'] = $params;
				
				p(http_build_query($p));
				  
				 $msg = curl_post1($url,http_build_query($p));
				 p($msg);
    	}
    	public function xcx_pay(){
    			$data['mch_id'] = '101520131244';
    			$data['order_sn'] = '201708280955374512';
    			$data['openid'] = 'opBrr0CIz6_PZ5n23H2fqhNrcfZc';
    			$data['appid'] = 'wx7aa4b28fb41fae496';
    			$data['is_minipg'] =  1;
    			$data['totalAmount'] = 0.01;
    			$data['reqId'] = '201708280956001629251';
    			$data['wx_key'] = 'd496bf0462c89aa1d71a4517c4e91f40';
    			$data['sign'] = $this->getSign($data);
    			p($data);
    			$res['data'] = $data;
    			$data = curl_post('http://test.ypt5566.com/api/Wxpay/wxjsapi',$data);
    			p($data);
    	}
    	public  function formatBizQueryParaMap($paraMap, $urlencode)
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
    	public function  getSign($arr)
		{
		
			define("APPID","YPT0031700010");
			define("APPKEY","964CD5FBC73544DA130ED5583EB2B9681B3CE0DD");
		    //过滤null和空
		    $Parameters = array_filter($arr, function ($v) {
		        if ($v === null || $v === '') {
		            return false;
		        }
		        return true;
		    });
		    //签名步骤一：按字典序排序参数
		    ksort($Parameters);
		    $String =$this->formatBizQueryParaMap($Parameters, false);
		    //签名步骤二：在string后加入KEY
		    $String = $String . "&key=" . APPKEY;
		    //签名步骤三：MD5加密
		    $String = md5($String);
		    //签名步骤四：所有字符转为大写
		    $result_ = strtoupper($String);
		    return $result_;
		}
}
