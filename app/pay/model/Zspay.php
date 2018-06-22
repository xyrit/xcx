<?php
namespace app\pay\model;
use think\Model;
class Zspay extends Model
{
		protected $name = 'merchants';
		protected $createTime = '';
		protected $updateTime = '';
		protected $auto = ['status' => 1];
	 	 /**
	     * 店铺信息
	     */
		public function micropay($data,$type)
	    {
	    	
			if($type==1){
						$param['mch_id'] = $data['merchant_id'];//商户号，由UCHANG分配
			            $param['device_info'] = $data['authCode'];
			            $param['nonce_str'] = date('Ymdhis');//随机字符串，不长于32位
			            $param['detail'] = '订单号:'.$data['order_sn'];
			            $param['body'] ='订单号:'.$data['order_sn'];//商品描述
			            $param['attach'] = '订单号:'.$data['order_sn'];
			            $param['out_trade_no'] = $data['order_sn'];//商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
			            $param['fee_type'] = "CNY";//符合ISO 4217标准的三位字母代码，默认人民币：CNY
			            $param['total_fee'] = $data['price'] * 100;//总金额，以分为单位，不允许包含任何字、符号
			            $param['spbill_create_ip'] = '127.0.0.1';//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
			            $param['auth_code'] = $data['authCode'];//扫码支付授权码， 设备读取用户展示的条码或者二维码信息
			}else{
					$param['mch_id'] = $data['merchant_id'];//商户号，由UCHANG分配
		            $param['device_info'] = $data['authCode'];
		            $param['nonce_str'] =date('Ymdhis');//随机字符串，不长于32位
		            $param['body'] = '订单号:'.$data['order_sn'];
		            $param['out_trade_no'] = $data['order_sn'];//商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
		            $param['fee_type'] = "CNY";//符合ISO 4217标准的三位字母代码，默认人民币：CNY
		            $param['total_fee'] = $data['price'] * 100;//总金额，以分为单位，不允许包含任何字、符号
		            $param['spbill_create_ip'] = '127.0.0.1';//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
		            $param['notify_url'] = "http://" . $_SERVER['SERVER_NAME'] . "/Api/Notify/zs_notify";//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
		            $param['trade_type'] = 'NATIVE';//取值如下：JSAPI，NATIVE，APP
		            $param['scene'] = 'bar_code';//支付场景 条码支付，取值：bar_code; 声波支付，取值：wave_code
		            $param['auth_code'] = $data['authCode'];//扫码支付授权码， 设备读取用户展示的条码或者二维码信息
			}
			
            $param['sign'] = $this->getSignVeryfy($param, $data['key']);
            $xmlData =arrayToXml($param);
           
            if($type==1){
	    		 $url = "http://api.cmbxm.mbcloud.com/wechat/orders/micropay";
	    	}else{
	    		 $url = "http://api.cmbxm.mbcloud.com/alipay/orders/micropay";
	    	}
            $result = curl_post1($url, $xmlData);
            $codeData = xmlToArray($result);
            yj_log('zs',$codeData);
         //   get_date_dir($this->path, "wechat", "微信刷卡返回", json_encode($codeData));
            $sign = $codeData['sign'];
            unset($codeData['sign']);
            $resign = $this->getSignVeryfy($codeData, $data['key']);
            if ($sign == $resign){
                if ($codeData['return_code'] == 'SUCCESS' && $codeData['result_code'] == 'SUCCESS') {
                    return ['transaction_id'=>$codeData['transaction_id'],'price'=>$codeData['total_fee']];
                } else if ($codeData['return_code'] == 'SUCCESS' && $codeData['result_code'] == 'FAIL' && $codeData['err_code'] == 'USERPAYING') {
                    $passData['mch_id'] = $data['merchant_id'];//商户号
                    $passData['out_trade_no'] = isset($codeData['out_trade_no'])?$codeData['out_trade_no']:$data['order_sn'];//商户系统内部的订单号
                 // $passData['transaction_id'] = $codeData['transaction_id'];//UCHANG订单号，优先使用
                    $passData['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
                    $passData['key'] = $data['key'];
                    $passData['paystyle_id'] = $type;
                    if($type==1){
                    	 $re = $this->password($passData);
                    }else{
                    	 $re = $this->ali_password($passData);
                    }
                  
                    if ($re['code'] == 'success'){
                       	 return ['transaction_id'=>$re['transaction_id'],'price'=>$re['total_fee']];
                    }else{
                    	return $this->error($re['err_code_des']);
                    }
                } else {
                	return $this->error($codeData['err_code_des']);
                }
            } else {
           		   return $this->error('平台验签失败');
            }
    }
    public function ali_password($payData){
	    	  $queryTimes = 6;
	        while ($queryTimes >= 0) {
	            $res = $this->ali_query($payData);
	           
	            if ($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
	                //如果需要等待5s后继续
	            		    $succResult = $res['trade_state'];
					            if ($succResult == 'TRADE_SUCCESS') {
				                  	 $res['code'] = 'success';
	                  				 return $res;
				                } else if ($succResult == 'TRADE_CLOSED') {
				                    return array("code" => "error",'err_code_des'=>'订单已经关闭');
				                } else if ($succResult == 'WAIT_BUYER_PAY') {
                                    if($queryTimes == 0){
                                        $this->cancel($payData);
                                        sleep(3);
                                        $res = $this->ali_query($payData);
                                        if ($res['trade_state'] == 'TRADE_SUCCESS') {
                                            $res['code'] = 'success';
                                            return $res;
                                        }else{
                                            return array("code" => "error",'err_code_des'=>'支付时间超过30秒，订单已撤销');
                                        }
                                    }else{
                                        sleep(5);
                                        $queryTimes--;
                                        continue;
                                    }
				                    //已关闭
				                } else if ($succResult == 'TRADE_FINISHED') {
				                    return array("code" => "error",'err_code_des'=>'支付失败，该订单已完成');
				                } else if ($succResult == 'PAYERROR') {
				                    return array("code" => "error",'err_code_des'=>'支付失败');
				                }
				                
	            } else {
	                return array("code" => "error",'err_code_des'=>$res['err_code_des']);
	            }
	        }
    }
    public function password($payData){
	    	  $queryTimes = 6;
	        while ($queryTimes >= 0) {
                $res = $this->query($payData);
	            if ($res['result_code'] == 'SUCCESS' && $res['return_code'] == 'SUCCESS') {
	                //如果需要等待5s后继续
	                $succResult = $res['trade_state'];
	                if ($succResult == 'SUCCESS') {
	                	$res['code'] = 'success';
	                    return $res;
	                } else if ($succResult == 'REFUND') {//转入退款
                        return array("code" => "error",'err_code_des'=>'付款金额已转入退款');
	                } else if ($succResult == 'NOTPAY') {//未支付
                        return array("code" => "error",'err_code_des'=>'客户已关闭支付');
	                } else if ($succResult == 'CLOSED') {//已关闭
                        return array("code" => "error",'err_code_des'=>'订单已经关闭');
	                } else if ($succResult == 'REVOKED') {//已撤销
                        return array("code" => "error",'err_code_des'=>'订单已撤销');
	                } else if ($succResult == 'USERPAYING') {//用户支付中
                        if($queryTimes == 0){
                            $this->cancel($payData);
                            sleep(3);
                            $res = $this->query($payData);
                            if($res['trade_state'] == 'SUCCESS'){
                                $res['code'] = 'success';
                                return $res;
                            }else{
                                return array("code" => "error",'err_code_des'=>'支付时间超过30秒，订单已撤销');
                            }
                        }else{
                            sleep(5);
                            $queryTimes--;
                            continue;
                        }
	                } else if ($succResult == 'PAYERROR'){//支付失败
	                    return array("code" => "error",'err_code_des'=>'支付失败');
	                }
	            } else {
	                return array("code" => "error",'err_code_des'=>$res['err_message']);
	            }
	        }
    }
    public function ali_query($param)
    {		
            $data['mch_id'] = $param['mch_id'];//商户号
            $data['out_trade_no'] = $param['out_trade_no'];//商户系统内部的订单号
            $data['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
            $data['sign'] = $this->getSignVeryfy($data, $param['key']);
            $xmlData = arrayToXml($data);
            $url = "http://api.cmbxm.mbcloud.com/alipay/orders/query";
            $result = curl_post1($url, $xmlData);
            $codeData = xmlToArray($result);
            yj_log('zs_ali_query',$codeData);
            return $codeData;
    }
	//交易结果查询
    public function query($param)
    {		
            $data['mch_id'] = $param['mch_id'];//商户号
            $data['out_trade_no'] = $param['out_trade_no'];//商户系统内部的订单号
            $data['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
            $data['sign'] = $this->getSignVeryfy($data, $param['key']);
            $xmlData = arrayToXml($data);
           	 $url = "http://api.cmbxm.mbcloud.com/wechat/orders/query";
            $result = curl_post1($url, $xmlData);
            $codeData = xmlToArray($result);
          	 yj_log('zs_query',$codeData);
            return $codeData;
    }
  //撤销接口
    public function cancel($param)
    {
    		
            $data['mch_id'] = $param['mch_id'];//商户号
            $data['out_trade_no'] = $param['out_trade_no'];//商户系统内部的订单号
            $data['nonce_str'] = date('YmdHis') . rand(10000, 99999);//随机字符串
            $data['sign'] = $this->getSignVeryfy($data, $param['key']);
            $xmlData = arrayToXml($data);
            if($param['paystyle_id'] == 1){
           		 	$url = "http://api.cmbxm.mbcloud.com/wechat/orders/reverse";
            }else{
            	 	$url = "http://api.cmbxm.mbcloud.com/alipay/orders/close";
           	}
            $result = curl_post1($url, $xmlData);
            $codeData = xmlToArray($result);
            yj_log('zs_cancel',$codeData);
     	    return $codeData;
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