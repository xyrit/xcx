<?php
namespace app\pay\model;
use think\Model;
class Pfpay extends Model
{
		protected $name = 'merchants';
		protected $createTime = '';
		protected $updateTime = '';
		protected $auto = ['status' => 1];
	   
	    /**
	     * 东莞中信刷卡
	     */
	    public function pf_micropay($data,$type){
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
                        yj_log('pf_query',$param);
				        $xmlData = arrayToXml($param);
				        $url = "https://pay.swiftpass.cn/pay/gateway";
				        $res = curl_post1($url, $xmlData);
				        $res = xmlToArray($res);
				        yj_log('pf',$res);
			      		if($res['result_code']==0){
							      		if(isset($res['need_query']) && $res['need_query']=='Y'){
					      				  		 $result = $this->password($data);
					      				  		 return $result;
					      				}
										$result['transaction_id'] = $res['transaction_id'];
										$result['price'] = $res['total_fee'];
					                    return $result;
						}elseif($res['result_code']==1){
							 //开始查询订单
							  if($res['err_code'] == 'USERPAYING' || $res['err_code'] == '10003'){
										$result = $this->password($data);
							  }else{
							  		return $this->error($res['err_msg']);
							  }
						}else{
									return $this->error($res['err_msg']);
						}
					    return $result; 
	    }
		public function password($data){
				 $queryTimes = 6;
				 while($queryTimes--){
				 		sleep(5);
				 		$res=$this->query($data);
				 		yj_log('pf_query',$res);
				 		if($res['status']==0){
				 			 	if($res['trade_state'] == 'USERPAYING' || $res['trade_state'] == '10003'){
				 			 			//继续
				 			 	}elseif($res['trade_state']=='SUCCESS'){
				 			 			$result['transaction_id'] = $res['transaction_id'];
										$result['price'] = $res['total_fee'];
					                    return $result;
				 			 	}elseif($res['trade_state']=='REVOKED'){
				 			 			return $this->error($res['trade_state_desc']);
				 			 	}else{
				 			 			return $this->error($res['trade_state_desc']);
				 			 	}
				 		}else{
				 				return $this->error($res['err_msg']);
				 		}
				 }
                    //开始撤单
                    $this->cancel($data);
                    sleep(3);//睡3秒预防网络等原因撤单未完成
                    $re = $this->query($data);//撤单后再查询一遍订单
                    yj_log('pf_query',$re);
                    if($re['trade_state']=='SUCCESS'){
                        $r['transaction_id'] = $re['transaction_id'];
                        $r['price'] = $re['total_fee'];
                        return $r;
                    }else{
                        return $this->error('支付时间超过30秒，订单已撤销');
                    }
		}
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
	      			yj_log('pf_cancel',$res);
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