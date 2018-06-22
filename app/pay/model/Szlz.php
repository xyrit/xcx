<?php
namespace app\pay\model;
use think\Model;
class Szlz extends Model
{
		protected $name = 'pay';
		protected $createTime = '';
		protected $updateTime = '';
		protected $auto = ['status' => 1];
		private $ali_public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB';
	    private $ali_private_key = 'MIICXAIBAAKBgQCtV+QoWwH8BpmfKfBglWUAMdKe2g+NeD0ajVxLKahKRHidU3SuXdu3Zy9E98k8R7E8mr0/EvFnshLaEUxaxc8QalEvbSx71s5NCYaHG3/aTSbjL1StFMSPvIh64DJx5o2jz66ppmGX4RkLV9/Xs58BF/oT3qDTZXTqQfEaiAsK9wIDAQABAoGALbvxs4AHbwIix+6dwC3KXxnGEyk/Tzj5DidbwWz1PNsB46hgMZ0L2kC8JPsnOeNEbNP6uEh8Lrq55JUJyy1Dawcn4a9IYYxaxxXh5cU/ucHtIJYsmjEcA4V/PdZCQv4McPPv0vERC/zVCpJ2MdXx26sNZvcT1NT8z38lhqgGb1ECQQDUxXu06+Vl/WD/SnsIx6u116kZwcS2cG+jja/20b9vBDh8eYKKIU3FfbTR6sskLyGxJEkgBgO8crNmYGm0MBAJAkEA0I+zrEJK87DgbDtP4TAmZN6FSot0hUOE6iskX4m/rv3KIhYug8F7AweHoBPPbUGCBoKdcqBhHGwFcBt6uHMC/wJAEnacmIOL4YDORPj6mjVxchMnymNlJYu2NFQcO+fRm9ma6TpGGKRxMj0JTtn4DMjGPK/wZIYBFv5BERY2tfshuQJAB7EVDkhPnVcrn7I8SvDMqbGvNsWX4YZQ85XtvHxHDnwbpVAuHPvYvo7biKLSZpQg6H6OsfiKPFMbjDvnNcBAHwJBALoTwUecjj5N6mgioBKuhgy3IXMMg1cclpMWhhxjbMo51tQ0IrpVW1IHl10zuOBQk3EpiFRm9YpQ5KWUGSlRdlY=';
	    private $aes = 'TOSFY0vpISFEZe28/TVB8Q==';
	    private $appid = '2017071207730667'; // 支付宝APPID
	    private $ali_notify_url = 'https://sy.youngport.com.cn/notify/szlzalipay_notify.php'; // 支付宝回调地址
	    public function  micropay($data,$type){
            yj_log('szlz',$data);
	    		if($type==1){
	    				include('./extend/SzWxPayPubHelper/WxPayPubHelper.php');
						spl_autoload_unregister('think\\Loader::autoload');
						$input = new \WxPayMicroPay();
						spl_autoload_register('think\\Loader::autoload');
				        $input->setParameter("auth_code", $data['authCode']);    // 授权码
				        $input->setParameter("body", '订单号:'.$data['order_sn']);  // 商品描述
				        $input->setParameter("total_fee", $data['price']*100); // 总金额
				        $input->setParameter("out_trade_no", $data['order_sn']);  // 商户订单号
				        $input->setParameter("sub_mch_id", $data['merchant_id']);    // 子商户号
				        $result = $input->pay();
				        if($result[0] == false){
	      						return $this->error($result[1]);
	       				}
	     			  	return ['transaction_id'=>$result[0],'price'=>$data['price']*100];
	    		}elseif($type==2){
	    			 $content = array(
				            'out_trade_no' => $data['order_sn'],
				            'scene' => 'bar_code',
				            'auth_code' => $data['authCode'],
				            'subject' => '订单号:'.$data['order_sn'],
				            'seller_id' => $data['merchant_id'],
				            'total_amount' => $data['price'],
							'extend_params' => array(
								'sys_service_provider_id' => '2088721521881652'
							)
       				 );
       				 $request = array(
				            'app_id' => $this->appid,
				            'method' => 'alipay.trade.pay',
				            'charset' => 'utf-8',
				            'sign_type' => 'RSA',
				            'app_auth_token' => $data['key'],
				            'timestamp' => date('Y-m-d H:i:s'),
				            'version' => '1.0',
				            'biz_content' => json_encode($content),
				      );
	    			  $string = $this->getSignContent($request);
       				  $sign = $this->rsaSign($string, $this->ali_private_key);
       				  $request['sign'] = $sign;
       				  yj_log('szlz',$request);
       				  add_log('szlz',json_encode($request));
       				  $url = 'https://openapi.alipay.com/gateway.do';
    				  $res_str = curl_post($url, $request);
    				  yj_log('szlz',$res_str);
    				  $result = json_decode($res_str, true);
    				  $result = $result['alipay_trade_pay_response'];
    				  if($result['code']=='10000'){
    				  	 	return  ['transaction_id'=>$result['out_trade_no'],'price'=>$data['price']*100];
    				  }else if($result['code'] == '10003'){
    				  	 	  if($transaction_id = $this->password($request,$data['order_sn'])){
    				  	 	  		return  ['transaction_id'=>$transaction_id,'price'=>$data['price']*100];
    				  	 	  }else{
    				  	 	  		return false;
    				  	 	  }
    				  }else{
    				  	 	return  $this->error($result['msg']);
    				  }
	    		}
	    }
		public function password($data, $remark)
	    {
			//③、确认支付是否成功
	        $queryTimes = 0;
	        while ($queryTimes < 28) {
	            $succResult = 0;
	            $queryResult = $this->ali_query($data, $succResult, $remark);
	            //如果需要等待2s后继续
	            if ($succResult == 2){
	                    if($queryTimes==24){
	                        break;
                        }else{
                            sleep(4);
                            $queryTimes += 4;
                            continue;
                        }
	             }else if($succResult == 1){//查询成功
	             		return  $queryResult['trade_no'];
	             }else{//订单交易失败
	            	  return $this->error('交易失败');
	             }
	        }
	        $this->ali_cancel($data, $remark);
            sleep(3);//睡3秒预防网络等原因撤单未完成
            $queryResult = $this->ali_query($data, $succResult, $remark);
            if($succResult == 1){//查询成功
                return  $queryResult['trade_no'];
            }
	        return $this->error('订单交易时间过长，已撤销订单');
	       //return array("code" => "error", "msg" => "失败", "data" => '');
	    }
	    
    public function ali_query($data, &$succResult, $remark)
    {
        $url = 'https://openapi.alipay.com/gateway.do';
        $content = array(
            'out_trade_no' => $remark,
        );
        $request = array(
            'app_id' => $this->appid,
            'method' => 'alipay.trade.query',
            'charset' => 'utf-8',
            'sign_type' => 'RSA',
            'app_auth_token' => $data['app_auth_token'],
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => json_encode($content),
        );
        $string = $this->getSignContent($request);
        $sign = $this->rsaSign($string, $this->ali_private_key);
        $request['sign'] = $sign;
        $return = curl_post($url, $request);
        yj_log('szlz',$return);
        $results = json_decode($return, true);
        add_log('szlz',$results);
        $result = $results['alipay_trade_query_response'];
       	if($result['code'] == '10000' && $result['trade_status'] == 'TRADE_SUCCESS'){
	            $succResult = 1;
	            return $result;
        } else if($result['code'] == '10000' && $result['trade_status'] == 'WAIT_BUYER_PAY'){
           	 	$succResult = 2;
        } else {
           	 	$succResult = 3;
        }
    }

    private function ali_cancel($data, $remark)
    {
        $url = 'https://openapi.alipay.com/gateway.do';
        $content = array(
            'out_trade_no' => $remark,
        );
        $request = array(
            'app_id' => $this->appid,
            'method' => 'alipay.trade.cancel',
            'charset' => 'utf-8',
            'sign_type' => 'RSA',
            'app_auth_token' => $data['app_auth_token'],
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'biz_content' => json_encode($content),
        );
        $string = $this->getSignContent($request);
        $sign = $this->rsaSign($string, $this->ali_private_key);
        $request['sign'] = $sign;
        $return = curl_post($url, $request);
        $results = json_decode($return, true);
        $result = $results['alipay_trade_cancel_response'];
        if($result['code'] == '10000'){
            return true;
        }else{
            return false;
        }
   }
	    /**
	     * 兴业刷卡
	     */
	    public function xy_micropay($data,$type){
						$param['service'] = 'unified.trade.micropay';
				        //商户号
				        $param['mch_id'] = $data['merchant_id'];
						 //商户订单号
				        $param['out_trade_no'] = $data['order_sn'];
				        //商品描述
				        $param['body'] = '订单号:'.$data['order_sn'];
				         //金额
				        $param['total_fee'] = (int)($data['price'] * 100);
				         //ip
				        $param['mch_create_ip'] = '127.0.0.1';
				        $param['auth_code'] = $data['authCode'];
				        //随机字符串
				        $param['nonce_str'] =time();
				        //签名
				        $param['sign'] = $this->getSignVeryfy($param,$data['key']);
				       
				        $xmlData = arrayToXml($param);
				        $url = "https://pay.swiftpass.cn/pay/gateway";
				        $res = curl_post1($url, $xmlData);
				        $res = xmlToArray($res);
			      		if($res['result_code']==0){
							      		if(isset($res['need_query']) && $res['need_query']=='Y'){
					      				  		 $result = $this->password($data);
					      				  		 return $result;
					      				}
										$result['transaction_id'] = $res['transaction_id'];
										$result['price'] =  $param['total_fee'];
					                    return $result;
						}elseif($res['result_code']==1){
							 //开始查询订单
							  if($res['err_code'] == 'USERPAYING'){
										$result = $this->password($data);
							  }else{
							  		return $this->error($res['err_msg']);
							  }
						}else{
									return $this->error($res['err_msg']);
						}
					    return $result; 
	    }
//		public function password($data){
//				 $queryTimes = 6;
//				 while($queryTimes--){
//				 		sleep(3);
//				 		$res=$this->query($data);
//				 		if($res['status']==0){
//				 			 	if($res['trade_state'] == 'USERPAYING'){
//				 			 			//继续
//				 			 	}elseif($res['trade_state']=='SUCCESS'){
//				 			 			$result['transaction_id'] = $res['transaction_id'];
//										$result['price'] = $res['total_fee'];
//				 			 			
//					                    return $result;
//				 			 	}elseif($res['trade_state']=='REVOKED'){
//				 			 			return $this->error($res['trade_state_desc']);
//				 			 	}else{
//				 			 			return $this->error($res['trade_state_desc']);
//				 			 	}
//				 		}else{
//				 				return $this->error($res['err_msg']);
//				 		}
//				 }
//				 //开始撤单	
//				 				 
//				 				 $this->cancel($data);
//								 return $this->error('订单已经撤销');
//		}
		 //查询query
	    public function query($param){
				    	$data['mch_id']  = $param['merchant_id'];
	    	 			$data['service'] = 'unified.trade.query';
			            $data['out_trade_no'] = $param['order_sn'];//商户系统内部的订单号
			           // $data['transaction_id'] = $param['out_trade_no'];//UCHANG订单号，优先使用
			            $data['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
			            $data['sign'] = $this->getSignVeryfy($data, $param['key']);
			            $xmlData = arrayToXml($data);
						$url = "https://pay.swiftpass.cn/pay/gateway";
						$res = curl_post1($url, $xmlData);
            yj_log('szlz',$res);
		      			$res = xmlToArray($res);

		      			return $res;
	    }
	     public function cancel($param){
    				$data['service'] = 'unified.micropay.reverse';
		            $data['mch_id'] = $param['merchant_id'];//商户号
		            $data['out_trade_no'] = $param['order_sn'];//商户系统内部的订单号
		           // $data['transaction_id'] = $param['out_trade_no'];//UCHANG订单号，优先使用
		            $data['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
		            $data['sign'] = $this->getSignVeryfy($data, $param['key']);
		    		$xmlData = arrayToXml($data);
					$url = "https://pay.swiftpass.cn/pay/gateway";
					$res = curl_post1($url, $xmlData);
	      			$res = xmlToArray($res);
//	      			p($res);
//	      			if ($res['status'] == '0'){
//						   	return $return;
//        			}else{
//				             return $res;
//        			}
   	 }
		public function sign($param,$key)
	    {
	        ksort($param);
	        $o = "";
	        foreach ($param as $k => $v) {
	            $o .= "$k=" . ($v) . "&";
	        }
	        $param = substr($o, 0, -1);
	        $post_data_temp = $param . $key;
	        $signIn = strtoupper(md5($post_data_temp));
	        return $signIn;
	     }
		 public function httpRequst($url, $post_data,$key = '1caf03b655fa5005e4ac9dde3d33c915')
		 {
		    	//p($key);
		        ksort($post_data);
		        $o = "";
		        foreach ($post_data as $k => $v) {
		            $o .= "$k=" . ($v) . "&";
		        }
		        $post_data = substr($o, 0, -1);
		        $post_data_temp = $post_data . $key;
		        $signIn = strtoupper(md5($post_data_temp));
		        $post_data = $post_data . "&signIn=" . $signIn;
		   
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_POST, 1);
		        curl_setopt($ch, CURLOPT_HEADER, 0);
		        curl_setopt($ch, CURLOPT_URL, $url);
		        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		        $result = curl_exec($ch);
		        curl_close($ch);
		        return $result;
		        //显示获得的数据   
		    }
	    public function info($store_id){
	    	return $this->where('id',$store_id)->find();
	    }
	     //支付接口统一签名
    private function getSignVeryfy($para_temp,$key)
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
  public function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }
    public function rsaSign($data, $privatekey)
    {
	        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
	            wordwrap($privatekey, 64, "\n", true) .
	            "\n-----END RSA PRIVATE KEY-----";
	
	        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
	        openssl_sign($data, $sign, $res);
	        $sign = base64_encode($sign);
	        return $sign;
    }
    //除去空字符串
    private function paraFilter($para)
    {
        $para_filter = array();
        while (list ($key, $val) = each($para)) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
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
     * return 拼接完成以后的字符串
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
	  	public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  	}
	    
}