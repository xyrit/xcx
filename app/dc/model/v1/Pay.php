<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Pay extends Model
{
		public $name = 'order';
		protected $createTime =  'add_time';
		protected $updateTime = 'update_time';
		private $orgNo; // 机构号7170
	    private $mercId; // 商户号
	    private $trmNo; // 终端设备号
	    private $signKey; // 密钥
		public function info($order_id,$uid){
				return $this->where('order_id',$order_id)->where('user_id',$uid)->find();
		}
		
		
		//生成order_sn
		public function get_order_sn(){
				return date('Ymdhis').rand(10000,99999);
		}

		private function xmlToArray($xml){   
	        //禁止引用外部xml实体
	        libxml_disable_entity_loader(true);
	        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
	        return $values;
  	    }

		//生成签名
		public function create_sign($order_id){
					// dump($order_id);
					//查询订单id
					$order = $this->where('order_id',$order_id)->field('order_sn,coupon_code,order_status,order_amount,order_sn,prepay_id,mid,user_id,integral,user_money,card_code')->find();
					// die;
					//判断积分和优惠券是否足够
					if($order->coupon_code&&(db::name('screen_user_coupons')->where('usercard',$order->coupon_code)->value('status')!=1)){
								return $this->error('优惠券已经使用了');
					}
					//首先查出会员卡
					if ($order->card_code) {
						$memcard_id =db::name('screen_memcard_use')->where('card_code',$order->card_code)->value('memcard_id');
						$cart_id = db::name('screen_memcard')->where('id',$memcard_id)->value('card_id');
					}
					if($order->integral>0){
								
						//首先查出会员卡
						// $cart_id = db::name('screen_memcard')->where('mid',$order->user_id)->value('card_id');
						
						if(empty($cart_id)){
							return $this->error('积分不足1');	
						}
						//查出用户unind
						$unionid = db::name('screen_mem')->where('id',$order->mid)->value('unionid');
						if(empty($unionid)){
							return $this->error('unionid');	
						}
						if($order->integral>db::name('screen_memcard_use')->where('unionid',$unionid)->where('card_id',$cart_id)->value('card_balance')){
								return $this->error('积分不足2');					
						}
						
					}
					//判断余额
					if ($order->user_money>0) {
						// dump($order->user_money);
						//首先查出店铺的
						// $cart_id1 = db::name('screen_memcard')->where('mid',$order->user_id)->value('card_id');
						
						if(empty($cart_id)){
							return $this->error('余额不足');	
						}
						//查出用户unind
						$unionid1 = db::name('screen_mem')->where('id',$order->mid)->value('unionid');
						if(empty($unionid1)){
							return $this->error('unionid');	
						}
						if($order->user_money>db::name('screen_memcard_use')->where('unionid',$unionid1)->where('card_id',$cart_id)->value('yue')){
								return $this->error('余额不足');					
						}
					}
					
					// dump($order_id);
					// echo "<br>";
					//查询商品信息
					$goods_name = db::name('order_goods')->where('order_id',$order_id)->value('goods_name');
					//查询用户的openid 和 商户的appid
					// dump($order->mid);
					$mem = db::name('screen_mem')->where('id',$order->mid)->find();
					if(!$openid = $mem['openid']){
							return $this->error('openid is empty');
					}
					
					
					//查询商户的支付方式
					$bank['wx_bank'] = array('in','3,9,7,2,10');
					$mid = db::name('merchants')->where('uid',$order->user_id)->value('id');
					if(!$cate = db::name('merchants_cate')->where(array('merchant_id'=>$mid,'checker_id'=>0))->where($bank)->order('id asc')->find()){

						return $this->error('没有开通支付功能');
					}
					// dump($mem);
					if ($mem['type']=='xcx') {
						$appid = db::name('appid')->where('uid',$mem['userid'])->value('appid');
					}else{
						$appid = db::name('dc_appid')->where('admin_id',$mem['userid'])->value('appid');
					}
					
					// dump($appid);
				
					switch($order->order_status){
						case 0:
						return $this->error('该订单已经取消');
						break;
						case 1:
						break;
						default:
						return $this->error('该订单已经支付');
					}
					
					$price  = $order->order_amount*100;
					// dump($price);die;
					//不需要支付
					if($price==0){
							if(model('order')->pay($order->order_sn,'','',0)){
								return 1;
							}else{
								return false;
							}
					}
					// dump($appid);
					// dump($cate['wx_bank']);
					switch($cate['wx_bank']){
						case '3':
						// return $this->error('暂未开通该支付方式!');
						include('./extend/WxPayPubHelper/WxPayPubHelper.php');
						if($order->prepay_id){
								return $this->create_xcx_sign($order->prepay_id,$appid);
						}
						//引入微信类
						spl_autoload_unregister('think\\Loader::autoload');
						$unifiedOrder = new \UnifiedOrder_pub();
						spl_autoload_register('think\\Loader::autoload');
				        $unifiedOrder->setParameter("body", '111'); //商品描述
				        $unifiedOrder->setParameter("out_trade_no", $order['order_sn']); //商户订单号
				        $unifiedOrder->setParameter("total_fee", $price); //总金额
				        $unifiedOrder->setParameter("notify_url", 'https://mp.youngport.com.cn/dc/v1/Base/wx'); //通知地址
				        $unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
				        $unifiedOrder->setParameter("sub_openid", $openid);
				        $unifiedOrder->setParameter("sub_appid", $appid);
				        $unifiedOrder->setParameter("sub_mch_id", $cate['wx_mchid']);//子商户号服务商必填
				        $unifiedOrderResult = $unifiedOrder->getResult(); //获取统一支付接口结果sub_appid
				        //商户根据实际情况设置相应的处理流程
				        if ($unifiedOrderResult["return_code"] == "FAIL"){
				        		// p($unifiedOrderResult);
				        		return $this->error($unifiedOrderResult['return_msg']);
				        } elseif ($unifiedOrderResult['prepay_id']) {
				        	$order->prepay_id = $unifiedOrderResult['prepay_id'];
				        	// dump($unifiedOrderResult);
				        	$order->save();
				         	return $this->create_xcx_sign($unifiedOrderResult['prepay_id'],$unifiedOrderResult['sub_appid']);
				        }
				        break;
				        case '9':
						// return $this->error('暂未开通该支付方式!');
						include('./extend/SzWxPayPubHelper/WxPayPubHelper.php');
						if($order->prepay_id){
								return $this->create_xcx_sign($order->prepay_id,$appid);
						}
						//引入微信类
						spl_autoload_unregister('think\\Loader::autoload');
						$unifiedOrder = new \UnifiedOrder_pub();
						spl_autoload_register('think\\Loader::autoload');
				        $unifiedOrder->setParameter("body", '111'); //商品描述
				        $unifiedOrder->setParameter("out_trade_no", $order['order_sn']); //商户订单号
				        $unifiedOrder->setParameter("total_fee", $price); //总金额
				        $unifiedOrder->setParameter("notify_url", 'https://mp.youngport.com.cn/dc/v1/Base/szwx'); //通知地址
				        $unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
				        $unifiedOrder->setParameter("sub_openid", $openid);
				        $unifiedOrder->setParameter("sub_appid", $appid);
				        $unifiedOrder->setParameter("sub_mch_id", $cate['wx_mchid']);//子商户号服务商必填
				        $unifiedOrderResult = $unifiedOrder->getResult(); //获取统一支付接口结果sub_appid
				        //商户根据实际情况设置相应的处理流程
				        if ($unifiedOrderResult["return_code"] == "FAIL"){
				        		// p($unifiedOrderResult);
				        		return $this->error($unifiedOrderResult);
				        } elseif ($unifiedOrderResult['prepay_id']) {
				        	$order->prepay_id = $unifiedOrderResult['prepay_id'];
				        	// dump($unifiedOrderResult);
				        	$order->save();
				         	return $this->create_xcx_sign($unifiedOrderResult['prepay_id'],$unifiedOrderResult['sub_appid']);
				        }
				        break;
				        case '1':
				        return $this->error('暂未开通该支付方式!');
	       					 $wzPay = new \Wzpay\Wzpay();
							 $wzPay->setParameter('sub_openid', $openid);
						     $wzPay->setParameter('mch_id', 'eyscvwvOs4BmEovMJOWeCoTvgvRtxWJu32RgygcugzN');
						     $wzPay->setParameter('body', $goods_name);
						     $wzPay->setParameter('out_trade_no', $order['order_sn']);
						     $wzPay->setParameter('goods_tag', $goods_name);
						     $wzPay->setParameter('total_fee', $order->order_amount*100);
						     $returnData = $wzPay->getParameters();
						     p($returnData);
				        break;
				        //招商
				        case '4':
				        return $this->error('暂未开通该支付方式!');
				        $param = [];
				        $param['sub_appid'] = $appid;
				        $param['mch_id'] = $cate['wx_mchid'];
				        $param['nonce_str'] = date("YmdHis").rand(1000,9999).'251';
				        $param['body'] = $order['order_sn'];//订单抬头
				        $param['out_trade_no'] = $order['order_sn'];
				        $param['total_fee'] = $price;
				        $param['notify_url'] = 'https://mp.youngport.com.cn/dc/v1/Base/zs';
				        $param['spbill_create_ip'] = '127.0.0.1';
				        $param['trade_type'] = 'JSAPI';
				        $param['sub_openid']=$openid;
				        $param['key_pay'] = $cate['wx_key'];
				        // dump($param);
				        $res = $this->weixin_c_b_pay($param);
				        // dump($res);
				      	$res = xmlToArray($res);
				      	// dump($res);
				      	if($res['return_code'] == 'FAIL'){
				      		return $this->error($res['return_msg']);
				      	}
				      	$order->prepay_id = $res['prepay_id'];
	   					$order->save();
//				      	$prepay_id = explode('=',$data->package)[1];
				        return  json_decode($res['js_prepay_info']);
				        break;
				        case '7':
				        	//测试
				        	//$json = '{"appId":"wx7aa4b28fb4fae496","timeStamp":"1503992997040","status":"0","signType":"MD5","package":"prepay_id=wx2017082915495743d903fc810446319551","callback_url":null,"nonceStr":"1503992997040","paySign":"464F9A7602E936B1697DF60E0C9A435B"}';
			        	//接口类型
					    	$param['service'] = 'pay.weixin.jspay';
					        $param['charset'] = 'UTF-8';
					        $param['mch_id'] = $cate['wx_mchid'];
					        //商户订单号
					        $param['out_trade_no'] = $order['order_sn'];
					        //商品描述
					        $param['body'] = '订单号：'.$order['order_sn'];
					        //用户openid
					        $param['sub_openid'] = $openid;
					        //公众账号或小程序ID
					        $param['sub_appid'] = $appid;
					        $param['mch_create_ip'] = '127.0.0.1';
					        //是否原生态
					        $param['is_raw'] = "1";
					        //金额
					        $param['is_minipg'] = "1";
					        $param['total_fee'] = (int)$price;
				     		//p($param);
				      		$param['notify_url']='https://mp.youngport.com.cn/dc/v1/Base/xy';
				      		
				        //订单生成时间
				//        $param['time_start'] = (string)date("YmdHis");
				        //订单超时时间
				//        $param['time_expire'] = (string)date("YmdHis", time() + 60);
				        //随机字符串
				        $param['nonce_str'] = date("YmdHis").rand(1000,9999).'251';
				        //签名
				      ///  p($data);?'4G4g9D648ef1ab6587257db9ac585e8025f291a8':'d496bf0462c89aa1d71a4517c4e91f40'
				        $param['sign'] = $this->getSignVeryfy_pay($param,$cate['wx_key']);
				        //转换成xml格式post提交数据
				        $xmlData = arrayToXml($param);
				      
				        $url = "https://pay.swiftpass.cn/pay/gateway";
				        $data = $this->httpRequst_pay($url, $xmlData);
				        
			 			$data = xmlToArray($data);
			 			if($data['status'] != 0){
				      		return $this->error($data['message']);
				      	}
			 			return json_decode($data['pay_info']);
				        break;
				        case '2';
//				        include('./extend/Wxpay/appWxPayPubHelper/WxPayPubHelper.php');
//				        if($order->prepay_id){
//								return $this->create_xcx_sign($order->prepay_id);
//						}
				        //add_log($order['order_sn']);
				        //根据商户
				        $param = [];
				        $param['action']='wallet/trans/jsSale';
        				$param['version']='2.0';
				        $param['reqTime']= date("YmdHis");
				        $param['appId']=$appid;
				        $param['uuid']=$openid;
				        $param['orderId']=$order['order_sn'];
				        $param['reqId']=date("YmdHis").rand(1000,9999).'251';
				        $param['deviceId']='payuser';//终端号
				        $param['transTimeOut']='1440';
				        $param['orderSubject']='订单号：'.$order['order_sn'];//订单抬头
				        $param['orderDesc']=$goods_name;//订单描述
				        $param['totalAmount']=$price;//交易金额
				        $param['bankCardLimit']=2;//银行卡限定类型，1 借记卡，2 借记卡和贷记卡，默认为 2
				        $param['currency']="CNY";
				        $param['notifyUrl']='https://mp.youngport.com.cn/dc/v1/Base/ms';
				        $param['acquirerType']='wechat';
				        $param['operatorId']="小程序";
				        
				        //查询商户的custid
				       	//$mid = db::name('merchants')->where('uid',$order->user_id)->value('id');
				      	//$custId = db::name('merchants_mpay')->where('uid',$mid)->value('wechat');
				      	
				        //$param['custId']='170615162016687';
				      //  add_log($custId);
				        $param['custId']=$cate['wx_mchid'];
				   	 	
				        $data=json_encode($param);
				        $data="[".$data."]";
				     	// p($data);
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
      					
      					$data = json_decode($result,true);
      					if($data[0]['responseCode']=="00"){
      						$data = json_decode($data[0]['payInfo']);
      						$prepay_id = explode('=',$data->package)[1];
      						$order->prepay_id = $prepay_id;
      						$a = $order->save();
      						return $data;
      					}else{
      						return $this->error($data[0]['errorMsg']);
      					}
      					//return $result;
				        break;
				        //浦发
				        case '10':
				        	//测试
				        	//$json = '{"appId":"wx7aa4b28fb4fae496","timeStamp":"1503992997040","status":"0","signType":"MD5","package":"prepay_id=wx2017082915495743d903fc810446319551","callback_url":null,"nonceStr":"1503992997040","paySign":"464F9A7602E936B1697DF60E0C9A435B"}';
			        	//接口类型
					    	$param['service'] = 'pay.weixin.jspay';
					        $param['charset'] = 'UTF-8';
					        $param['mch_id'] = $cate['wx_mchid'];
					        //商户订单号
					        $param['out_trade_no'] = $order['order_sn'];
					        //商品描述
					        $param['body'] = '订单号：'.$order['order_sn'];
					        //用户openid
					        $param['sub_openid'] = $openid;
					        //公众账号或小程序ID
					        $param['sub_appid'] = $appid;
					        $param['mch_create_ip'] = '127.0.0.1';
					        //是否原生态
					        $param['is_raw'] = "1";
					        //金额
					        $param['is_minipg'] = "1";
					        $param['total_fee'] = (int)$price;
				     		//p($param);
				      		$param['notify_url']='https://mp.youngport.com.cn/dc/v1/Base/pf';
				      		file_put_contents('1.txt',$openid.'---'.$appid.'这里了');
				        //订单生成时间
				//        $param['time_start'] = (string)date("YmdHis");
				        //订单超时时间
				//        $param['time_expire'] = (string)date("YmdHis", time() + 60);
				        //随机字符串
				        $param['nonce_str'] = date("YmdHis").rand(1000,9999).'251';
				        //签名
				      ///  p($data);?'4G4g9D648ef1ab6587257db9ac585e8025f291a8':'d496bf0462c89aa1d71a4517c4e91f40'
				        $param['sign'] = $this->getSignVeryfy_pay($param,$cate['wx_key']);
				        //转换成xml格式post提交数据
				        $xmlData = arrayToXml($param);
				      
				        $url = "https://pay.swiftpass.cn/pay/gateway";
				        $data = $this->httpRequst_pay($url, $xmlData);
				        
			 			$data = xmlToArray($data);
			 			if($data['status'] != 0){
				      		return $this->error($data['message']);
				      	}
			 			return json_decode($data['pay_info']);
				        break;
				        //新大陆
				        case '11':
				        return $this->error('暂未开通该支付方式!');
				        $this->getInfo($mid);
				        // $this->url = 'https://gateway.starpos.com.cn/sysmng/bhpspos4/5533020.do';
				        $params['opsys'] = '0';
				        $params['characterSet'] = '00';
				        $params['orgno'] = $this->orgNo;
            			$params['mercid'] = $this->mercId;
            			$params['trmno'] = $this->trmNo;
				        $params['tradeno'] = $this->getRemark();
				        $params['trmtyp'] = 'W';
				        $params['txntime'] = date('YmdHis');
				        $params['signtype'] = 'MD5';
				        $params['version'] = 'V1.0.0';
				        $params['amount'] = $price;
				        $params['total_amount'] = $price;
				        $params['paychannel'] = 'WXPAY'; //支付宝	ALIPAY 微信	WXPAY 银联	YLPAY
				        $params['paysuccurl'] = 'https://mp.youngport.com.cn/dc/v1/Base/xdl';
				        $params['signvalue'] = $this->getSign($params);
				        // $this->writlog('JS_wx_pay.log', 'payParams：' . json_encode($params));
				        file_put_contents('./data/log/JS_wx_pay.log', date("Y-m-d H:i:s") . json_encode($params). PHP_EOL, FILE_APPEND | LOCK_EX);
				        return array('data'=>$params,'url'=>'https://gateway.starpos.com.cn/sysmng/bhpspos4/5533020.do');
				        break;
				        default:
				        return $this->error('暂未开通该支付方式!');
				        break;
					}
		}

		private function getInfo($merchant_id)
	    {
	        $re = db::name('merchants_xdl')->where(array('m_id' => $merchant_id))->find();
	        $this->orgNo = $re['orgNo'];
	        $this->mercId = $re['mercId'];
	        $this->trmNo = $re['trmNo'];
	        $this->signKey = $re['signKey'];
	    }

		private function getSign($params)
	    {
	        ksort($params);
	        // $this->writlog('JS_wx_pay.log', 'SortParams：' . json_encode($params));
	        $str = '';
	        foreach ($params as $v) {
	            $str .= $v;
	        }
	        // ECHO $str . $this->signKey;
	        return md5($str . $this->signKey);
	    }

		private function getRemark()
	    {
	        return date('YmdHis') . rand(100000, 999999);
	    }

	  private function weixin_c_b_pay($data){
        $param['mch_id'] = $data['mch_id'];//商户号，由UCHANG分配
        //否
        if(isset($data['sub_appid']) && !empty($data['sub_appid'])){
            $param['sub_appid']=$data['sub_appid'];//商户微信公众号appid,app支付时,为在微信开放平台上申请的APPID
        }
        //否
        if(isset($data['device_info']) && !empty($data['device_info'])){
            $param['device_info']=$data['device_info'];//终端设备号(门店号或收银设备ID)，注意：PC网页或公众号内支付请传“WEB”
        }
        //是
        $param['nonce_str']=$data['nonce_str'];//随机字符串，不长于32位
        //是
        $param['body']=$data['body'];//商品描述
        //否
        if(isset($data['detail']) && !empty($data['detail'])){
            $param['detail']=$data['detail'];//商品详细列表，使用Json格式，传输签名前请务必使用CDATA标签将JSON文本串保护起来。goods_detail 服务商必填 []：└ goods_id String 必填 32 商品的编号└ wxpay_goods_id String 可选 32 微信支付定义的统一商品编号└ goods_name String 必填 256 商品名称└ quantity Int 必填 商品数量└ price Int 必填 商品单价，单位为分└ goods_category String 可选 32 商品类目ID└ body String 可选 1000 商品描述信息
        }
        //否
        if(isset($data['attach']) && !empty($data['attach'])){
            $param['attach']=$data['attach'];//附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
        }
        //是
        $param['out_trade_no']=$data['out_trade_no'];//商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号
        //是
        $param['fee_type']="CNY";//符合ISO 4217标准的三位字母代码，默认人民币：CNY
        //是
        $param['total_fee']=$data['total_fee'];//总金额，以分为单位，不允许包含任何字、符号
        //是
        $param['spbill_create_ip']='127.0.0.1';//APP和网页支付提交用户端ip，Native支付填调用微信支付API的机器IP。
        //是
        // $param['time_start']=date("YmdHis");//订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010
        // //是
        // $param['time_expire']=date("YmdHis");//如上
        //否
        if(isset($data['goods_tag']) && !empty($data['goods_tag'])){
            $param['goods_tag']=$data['goods_tag'];//商品标记，代金券或立减优惠功能的参数
        }
        //是
        $param['notify_url']=$data['notify_url'];//接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数。
        //是
        $param['trade_type']=$data['trade_type'];//取值如下：JSAPI，NATIVE，APP
        //否
        if(isset($data['product_id']) && !empty($data['product_id'])){
            $param['product_id']=$data['product_id'];//trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义。
        }
        //否
        if(isset($data['limit_pay']) && !empty($data['limit_pay'])){
            $param['limit_pay']=$data['limit_pay'];//no_credit–指定不能使用信用卡支付
        }
        //否
        if(isset($data['sub_openid']) && !empty($data['sub_openid'])){
            $param['sub_openid'] = $data['sub_openid'];//trade_type=JSAPI，此参数必传，用户在子商户appid下的唯一标识。openid和sub_openid可以选传其中之一，如果选择传sub_openid,则必须传sub_appid。
         
        }
            //否
        $param['wxapp']="true";//true–小程序支付；此字段控制 js_prepay_info 的生成，为true时js_prepay_info返回小程序支付参数，否则返回公众号支付参数
        
        //获取签名
        $param['sign']=$this->getSignVeryfy_pay($param,$data['key_pay']);
        //转换成xml格式post提交数据
        
        $xmlData=arrayToXml($param);
        var_dump($xmlData);
        $url="http://api.cmbxm.mbcloud.com/wechat/orders";
        $result=$this->httpRequst_pay($url, $xmlData);
        
        return $result;
    }
    
     private function httpRequst_pay($url, $post_data)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
        //显示获得的数据   
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
	
	private function  getSignVeryfy_pay($para_temp,$paykey){
        //除去待签名参数数组中的空值和签名参数
        $para_filter =$this->paraFilter($para_temp);
        
        //对待签名参数数组排序
        $para_sort =$this->argSort($para_filter);
       
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr =$this->createLinkstring($para_sort);
        //拼接apikey
        $prestr=$prestr."&key=".$paykey;
        //MD5 转大写
        $prestr=strtoupper(md5($prestr));
        return $prestr;
    }
  
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    private function createLinkstring($para){
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        
        return $arg;
    }
	//http 请求
	private function httpRequst($url,$data,$res,$appkey){
				$ch = curl_init();
				$headers[] = "Accept-Charset: utf-8";
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
				curl_setopt($ch, CURLOPT_SSLVERSION, 1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, 'params='.$data);
				curl_setopt($ch, CURLOPT_HTTPHEADER,array(
			        "Content-Type:application/x-www-form-Urlencoded;charset=utf-8",
			        "Accept-Language:zh-cn",
			        "x-apsignature:".$res,
			        "x-appkey:".$appkey
			    ));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$output = curl_exec($ch);
				curl_close($ch);
		        return $output;
		}
		//小程序签名生成
		public function create_xcx_sign($package,$appid){
				spl_autoload_unregister('think\\Loader::autoload');
				$Common_util_pub = new \Common_util_pub();
				spl_autoload_register('think\\Loader::autoload');
				$data = [];
				$data['appId'] = $appid;
	            $data['nonceStr'] = $Common_util_pub->createNoncestr();
	            $data['package'] = 'prepay_id='.$package;
	            $data['signType'] = 'MD5';
	            $data['timeStamp'] = (string)time();
	            $data['paySign'] = $Common_util_pub->getSign($data);
	            // dump($data);die;
	            return $data;
		}
		
		//确认收货
		public function comfirm($order_id,$uid){
			
				$order = $this->where('order_id',$order_id)->where('mid',$uid)->field('order_status')->find();
			
				if($order['order_status']==3){
						$order->order_status = 4;
						return $order->save()?true:$this->error('修改失败');
				}else{
						$this->error('该订单不能确认收货');
				}
		}
		//支付成功
		public function pay_old($out_trade_no,$transaction_id,$price,$bank_id=0,$paystyle = 1)
		{
					//查询订单是否支付
    				if(!$order = $this->where(array('order_sn'=>$out_trade_no,'type'=>2))->field('order_id,order_amount,mid,user_id,coupon_code,order_status,mid,card_code,integral,mobile,add_time,user_money')->find()){
    								err('没有找到订单');
    				}
    				
    				if($order->order_status!==1){
    						err('已经支付了');
    				}
    				
    				// file_put_contents('1.txt','未支付');
					//开启事务
    				$this->startTrans();
    				//修改订单状态
    				$order->pay_time = time();
    				$order->order_status = 5;
    				$order->paystyle = $paystyle;
    				$order->transaction = $transaction_id;
    				$order->real_price = $price;
    				$order->save();
    				//修改商品销量
    				// file_put_contents('1.txt',$order->order_id);
    				$order_goods = db::name('order_goods')->where('order_id',$order->order_id)->select();
    				foreach ($order_goods as $key => $value) {
    					// file_put_contents('2.txt',$value['goods_id']);
    					$goods = db::name('goods')->where('goods_id',$value['goods_id'])->find();
    					$goods['sales'] += $value['goods_num'];
    					// file_put_contents('3.txt',$goods['sales']);
    					if(!db::name('goods')->where('goods_id',$value['goods_id'])->update(['sales'=> $goods['sales']])){
    						// file_put_contents('5.txt','修改商品销量失败');	
    							$this->rollback();
    							return $this->error('修改商品销量失败');
    					}
    					$good = db::name('goods')->where('goods_id',$value['goods_id'])->find();
    					// file_put_contents('4.txt',$good['sales']);	
    				}
    				
    				$ScreenMemcardUse = model('ScreenMemcardUse');
    				$card = $ScreenMemcardUse->lists($order->user_id,$order->mid);
    				//查看是否存在会员卡
    				if($card){
						$card = $card[0];
						//获得积分
						if($card['credits_set'] == 1){
							$integral = (int)($price/$card['expense'])*$card['expense_credits'];
							add_log($integral);
							$integral&&$ScreenMemcardUse->updateuser($order->card_code,$integral,$out_trade_no);
						}
    					//扣除积分，余额
	    				if($order->card_code&&$order->integral){
	    						add_log(-$order->integral);
	    						 
	    						$ScreenMemcardUse->updateuser($order->card_code,-$order->integral,$out_trade_no);
	    				}
	    				// file_put_contents('7.txt',$order['integral']);
	    				// file_put_contents('6.txt',-$order->user_money);
	    				if($order->card_code&&$order->user_money){
	    						add_log(-$order->user_money);
	    						$ScreenMemcardUse->updateuser1($order->card_code,-$order->user_money,$out_trade_no);
	    				}
	    					
    				}
    				
					if($order->coupon_code){
    						//$openid = db::name('screen_mem')->where('id',$order->mid)->value('openid');
    						//查看优惠券信息
    						$coupons = db::name('screen_user_coupons')->where('usercard',$order->coupon_code)->find();
    						if($coupons){
			    						//修改优惠券
			    						if(db::name('screen_user_coupons')->where('id',$coupons['id'])->setField('status',0)==false){
			    								add_log($out_trade_no.'修改优惠券状态失败');
			    						}
			    						
			    						//获得小程序token
			    						$token = model('wx')->get_token();
			    						$data['code'] = $coupons['usercard'];
			    						//开始核销优惠券
			    						$status = curl_post1('https://api.weixin.qq.com/card/code/consume?access_token='.$token,json_encode($data));
			    						add_log($status);
    						}
    						
    				}
    				//记录流水
    				if(!db::name('pay')->where('order_id',$order->order_id)->find()){
    							$pay['merchant_id'] = db::name('merchants')->where('uid',$order->user_id)->value('id');
    							//查询openid
    							$pay['customer_id'] = $order->mid;
    							$pay['paystyle_id'] = 1;
    							$pay['order_id'] = $order->order_id;
    							$pay['mode'] = 11;
    							$pay['phone_info'] = $order->mobile;
    							$pay['price'] = $price;
    							$pay['remark'] = $out_trade_no;
    							$pay['add_time'] = $order->add_time;
    							$pay['paytime'] = time();
    							$pay['bill_date'] = date('Ymd');
    							$pay['new_order_sn'] = $out_trade_no;
    							$pay['transId'] = $transaction_id;
    							$pay['status'] = 1;
    							$pay['bank'] = $bank_id;
    							//先获得cate_id
    							$pay['cate_id'] = db('merchants_cate')->where('wx_bank',$bank_id)->where('merchant_id',$pay['merchant_id'])->order('id')->value('id'); 
    							$pay['cost_rate'] = db('merchants_pfpay')->where('merchant_id',$pay['merchant_id'])->value('wx_code');
    							file_put_contents('pay.txt',$pay['cost_rate']);
    							file_put_contents('pay1.txt','费率');
    							db::name('pay')->insert($pay);
    				}
    				
    				$this->commit();
    				return $order->order_id;
		}
		public function cancel($order_id,$uid){
				$data  = $this->where('order_id',$order_id)->where('mid',$uid)->find();
				if($data->order_status==1){
					//开启事务
    				$this->startTrans();
					//修改订单状态
					$data->order_status=0;
					//返回库存
					$order_goods = db::name('order_goods')->where('order_id',$data->order_id)->select();
					//返回商品库存
		    		foreach($order_goods as $v){
		    					if(!db::name('goods')->where('goods_id',$v['goods_id'])->setInc('goods_number',$v['goods_num'])){
		    							$this->rollback();
		    							return $this->error('修改商品库存失败');
		    					}
		    					if($v['spec_key']){
		    							if(!db::name('goods_sku')->where('sku_id',$v['spec_key'])->setInc('quantity',$v['goods_num'])){
		    									$this->rollback();
		    									return $this->error('修改商品库存失败');
		    							}
		    					}
		    		}
		    		$data->save();
		    		$this->commit();
					return true;
				}else{
					return $this->error('该订单不能取消');
				}
		}
		private function zs_sign($para_temp,$paykey) {
		        //除去待签名参数数组中的空值和签名参数
		        $para_filter = $this->paraFilter($para_temp);
		        
		        //对待签名参数数组排序
		        $para_sort =$this->argSort($para_filter);
		        
		        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		        $prestr =$this->createLinkstring($para_sort);
		        //拼接apikey
		        $prestr=$prestr."&key=".$paykey;
		        //MD5 转大写
		        $prestr=strtoupper(md5($prestr));
		        return $prestr;
   		}
   		//27c539b541d35ddc03c7951fa22248b5
	   	private function paraFilter($para) {
	        $para_filter = array();
	        while(list ($key, $val) = each ($para)) {
	            if($key == "sign" || $key == "sign_type" || $val == "")continue;
	            else    $para_filter[$key] = $para[$key];
	        }
	        return $para_filter;
	    }
	    
    	  //数组排序
	    private function argSort($para) {
	        ksort($para);
	        reset($para);
	        return $para;
	    }
	    
		public function error($msg){
				$this->error = $msg;
				return false;
		}
	
}