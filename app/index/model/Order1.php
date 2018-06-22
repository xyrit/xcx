<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Order extends Model
{
		protected $createTime =  'add_time';
		protected $updateTime = 'update_time';
		public function info($order_id,$uid){
				return $this->where('order_id',$order_id)->where('user_id',$uid)->find();
		}
		public function info_detail($order_id,$uid,$field=true){
				if(!$order = $this->where('order_id',$order_id)->where('mid',$uid)->field($field)->find()){
							return $this->error('没有该订单');
				}
				$goods = db::name('order_goods')->where('order_id',$order_id)->select();
				
				foreach($goods as $key => &$v){
						$v['goods_img'] = URL.$v['goods_img'];
						
				}
				$order->goods = $goods; 
				return $order;
		}
		public function lists($store_id,$uid,$type=0){
				$where = [];
				$where['mid'] = $uid;
				$where['type'] = 1;
				$where['user_id'] = $store_id;
				switch((int)$type){
					case 1:
					$where['order_status'] = 1;
					break;
					case 2:
					$where['order_status'] = array('in','2,3');
					break;
					default:
					//$where['order_status'] = array('neq',0);
					break;
				}
				$data = $this->where($where)->order('update_time desc')->paginate(10);
				foreach($data as &$v){
						$order_goods = db::name('order_goods')->field('goods_name,goods_num,spec_key_name,goods_price,goods_img')->where(['order_id'=>$v['order_id']])->select();
						
						foreach($order_goods as &$vv){
								$vv['goods_img'] = URL.$vv['goods_img'];
						}
						
						$v['order_goods'] = $order_goods;
						//修改状态
						switch((int)$v['order_status']){
								case 0:
								$v['order_status_name'] = '已取消';
								break;
								case 1;
								$v['order_status_name'] = '未付款';
								break;
								case 2:
								$v['order_status_name'] = '待发货';
								break;
								case 3:
								$v['order_status_name'] = '已发货';
								break;
								case 4:
								$v['order_status_name'] = '已签收';
								break;
								case 5:
								$v['order_status_name'] = '交易完成';
								break;
						}
				}
				return $data;
		}
		public function add1($goods_id,$attr_id=0,$nums=1){
			   	//查询商品的信息
				$data = $this->where('goods_id',$goods_id)->where('attr_id',$attr_id)->find();
				
				//更新
				if($data){
						$data->nums += $nums;
						if($data->nums<=0){
					  		return $this->error('商品数量最小为1');
					    }
					    $goods = model('goods');
						if(!$goods_info = $goods->check_good($goods_id,$attr_id,$data->nums)){
					  		return $this->error($goods->getError());
					 	}
					 	if($data->save()){
					  			return true;
						}else{
						  	return $this->error('添加失败');
						}
				//添加
				}else{
					  if($nums<=0){
					  		return $this->error('商品数量最小为1');
					  }
					//查看库存
					  if(!$goods_info = model('goods')->check_good($goods_id,$attr_id,$nums)){
					  		return $this->error($goods_info->getError());
					  }
					  
					  $this->uid = UID;
					  $this->goods_id = $goods_id;
					  $this->store_id = $goods_info->mid;
					  $this->attr_id = $attr_id;
					  $this->nums = $nums;
					  $this->goods_info = json_encode($goods_info);
					
					  if($this->save()){
					  	return true;
					  }else{
					  	return $this->error('添加失败');
					  }
				}
		}
		//添加订单
		public function add($goods_id,$nums_id,$attr_id,$address_id,$store_id,$coupons_id){
				//商品id
				$goods_id = explode(',',$goods_id);
				$attr_id = explode(',',$attr_id);
				$nums = explode(',',$nums_id);
				$order_goods = $order_good = $order = [];
				$order['total_amount'] = 0;
				
				//查询收货地址
				if(!$address = db::name('address')->where('id',$address_id)->where('uid',UID)->find()){
							return $this->error('该收货地址不存在');
				}
				//开始添加数据
				$this->startTrans();
				//查看商品
				$order['shipping_price'] = 0;
				$order['coupon_price'] = 0;
				foreach($goods_id as $key =>$v){
						if(!$goods = db::name('goods')->where('goods_id',$v)->lock(true)->find()){
								return	$this->error('该商品不存在');			
						}
						if((bool)$goods['is_sku']!==(bool)$attr_id[$key]){
								return $this->error($goods['goods_name'].'该商品已经卖完了');
						}
						if($goods['goods_number']<$nums[$key]){
										return $this->error($goods['goods_name'].'库存不足');	
						}
						if($goods['is_sku']){
								$goods_sku = db::name('goods_sku')->where('goods_id',$v)->lock(true)->where('sku_id',$attr_id[$key])->find();
								//判断库粗是否足够
								if($goods_sku['quantity']<$nums[$key]){
										return $this->error($goods['goods_name'].'库存不足');	
								}
								$price = $goods_sku['price'];
								$property = $goods_sku['properties'];
								$spec_key = $attr_id[$key];
						}else{
								$price = $goods['shop_price'];
								$property = '';
								$spec_key = 0;
						}
						$order['shipping_price'] = $goods['freight']>$order['shipping_price']?$goods['freight']:$order['shipping_price'];
						$order['total_amount'] += $price*$nums[$key];
						$order['type'] = 1;
						
						$order_good['spec_key'] = $spec_key;
						$order_good['goods_id'] = $v;
						$order_good['goods_name'] = $goods['goods_name'];
						$order_good['goods_num'] = $nums[$key];
						$order_good['goods_price'] = $price;
						$order_good['spec_key_name'] = $property;
						$order_good['goods_img'] = $goods['goods_img1'];
						$order_goods[] = $order_good;		
				}
				$order['order_amount'] = $order['total_amount'];
				
				//查看优惠券是否满足要求
				if($coupons_id>0){
					$ScreenUserCoupons = model('ScreenUserCoupons');
					if(($Coupons = $ScreenUserCoupons->check($coupons_id,UID,$store_id,$order['total_amount']))===false){
								return $this->error($ScreenUserCoupons->getError());
					}
					$order['coupon_price'] = $Coupons['price'];
					$order['order_amount'] -= $order['coupon_price'];
				}
				
//				//会员卡
//				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
//				$card_balance = $card_price =$card_code = 0;
//				if($Memcard){
//							
//							$Memcard = $Memcard[0];
//							$card_code = 
//							$card_balance = $Memcard['card_balance']-($Memcard['card_balance']%$Memcard['credits_use']);
//							$card_price = (int)($card_balance/$Memcard['credits_use']) * $Memcard['credits_discount'];
//							
//							if($card_price>$order['total_amount']){
//								$card_price = $order['total_amount']-0.01;
//								$card_balance = ceil($card_price/$Memcard['credits_discount'])*$Memcard['credits_use'];
//								
//							}
//								
//				}
//				
				//会员卡
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				$card_balance = $card_price = $card_code = 0;
				$price = $order['order_amount'];
				if($Memcard&&$price>=$Memcard[0]['credits_discount']){
							$Memcard = $Memcard[0];
							while(($Memcard['card_balance']-=$Memcard['credits_use'])>=0&&($price-=$Memcard['credits_discount'])>=0){
											$card_price+=$Memcard['credits_discount'];
											$card_balance+=$Memcard['credits_use'];
							}
							$card_code = $Memcard['card_code'];
				}
				
				$order['card_code'] = $card_code;
				$order['integral'] = $card_balance;
				$order['integral_money'] = $card_price;
				$order['coupon_code'] = isset($Coupons)?$Coupons['usercard']:'';
				$order['order_sn'] = $this->get_order_sn();
				$order['mid'] = UID;
				$order['user_id'] = $store_id;
				$order['consignee'] = $address['name'];
				$order['mobile'] = $address['tel'];
				$order['address'] = $address['address'].$address['addresses'];
				$order['area_id']  =$address['area_id'];
				//减去优惠权，积分，加上邮费
				$order['order_amount'] = $order['order_amount']+$order['shipping_price']-$card_price;
				
				$order['order_status'] = 1;
				$order['paystyle'] = 0;
				
    			//添加订单
    			if(!$this->save($order)){
    					$this->rollback();
    					return $this->error('添加订单失败');
    			}
    			$order_id = $this->getLastInsID();
    			foreach($order_goods as $key=>$v){
    					$order_goods[$key]['order_id'] = $order_id;
    			}
    			if(!db::name('order_goods')->insertAll($order_goods)){
    					$this->rollback();
    					return $this->error('添加商品失败');
    			}
    			//减去商品库存
    			foreach($order_goods as $v){
    					//删除购物车
    					
    					if(!db::name('goods')->where('goods_id',$v['goods_id'])->setDec('goods_number',$v['goods_num'])){
    							$this->rollback();
    							return $this->error('修改商品库存失败');
    					}
    					if($v['spec_key']){
    							if(!db::name('goods_sku')->where('sku_id',$v['spec_key'])->setDec('quantity',$v['goods_num'])){
    									$this->rollback();
    									return $this->error('修改商品库存失败');
    							}
    							//删除购物车
    							
    					}
    					db::name('cart')->where('goods_id',$v['goods_id'])->where('attr_id',$v['spec_key'])->where('uid',UID)->delete();
    			}
    			
    			
    			//合兑
    			$this->commit();
    			//生成签名
    			
				return $order_id;
				
		}
		//生成order_sn
		public function get_order_sn(){
				return date('Ymdhis').rand(10000,99999);
		}
		//生成签名
		public function create_sign($order_id,$type){
					//查询订单id
					$order = $this->where('order_id',$order_id)->field('order_sn,coupon_code,order_status,order_amount,order_sn,prepay_id,mid,user_id,integral')->find();
					//判断积分和优惠券是否足够
					
					if($order->coupon_code&&(db::name('screen_user_coupons')->where('usercard',$order->coupon_code)->value('status')!=1)){
								return $this->error('优惠券已经使用了');
					}
					
					if($order->integral){
								
								//首先查出店铺的优惠券
								$cart_id = db::name('screen_memcard')->where('mid',$order->user_id)->value('card_id');
								
								if(empty($cart_id)){
									return $this->error('积分不足');	
								}
								//查出用户unind
								$unionid = db::name('screen_mem')->where('id',$order->mid)->value('unionid');
								if(empty($unionid)){
									return $this->error('unionid');	
								}
								if($order->integral>db::name('screen_memcard_use')->where('unionid',$unionid)->where('card_id',$cart_id)->value('card_balance')){
										return $this->error('积分不足');					
								}
					}
					
					//查询商品信息
					$goods_name = db::name('order_goods')->where('order_id',$order_id)->value('goods_name');
					//查询用户的openid 和 商户的appid
					$mem = db::name('screen_mem')->where('id',$order->mid)->find();
					if(!$openid = $mem['openid']){
							return $this->error('openid is empty');
					}
					$appid = db::name('appid')->where('uid',$mem['userid'])->value('appid');
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
					//不需要支付
					if($price==0){
							if($this->pay($order->order_sn,'','',0)){
								return 1;
							}else{
								return false;
							}
					}
					switch($type){
						case 'wx':
						return $this->error('暂未开通该支付方式!');
						include('./extend/Wxpay/appWxPayPubHelper/WxPayPubHelper.php');
						if($order->prepay_id){
								return $this->create_xcx_sign($order->prepay_id);
						}
						//引入微信类
						spl_autoload_unregister('think\\Loader::autoload');
						$unifiedOrder = new \UnifiedOrder_pub();
						spl_autoload_register('think\\Loader::autoload');
				        $unifiedOrder->setParameter("body", '111'); //商品描述
				        $unifiedOrder->setParameter("out_trade_no", $order['order_sn']); //商户订单号
				        $unifiedOrder->setParameter("total_fee", $price); //总金额
				        $unifiedOrder->setParameter("notify_url", 'https://mp.youngport.com.cn/index/Notify/wx'); //通知地址
				        $unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
				        $unifiedOrder->setParameter("openid", $openid);
				        $unifiedOrderResult = $unifiedOrder->getResult(); //获取统一支付接口结果
				        //商户根据实际情况设置相应的处理流程
				        if ($unifiedOrderResult["return_code"] == "FAIL"){
				        		p($unifiedOrderResult);
				        		return $this->error($unifiedOrderResult);
				        } elseif ($unifiedOrderResult['prepay_id']) {
				        	$order->prepay_id = $unifiedOrderResult['prepay_id'];
				        	$order->save();
				         	return $this->create_xcx_sign($unifiedOrderResult['prepay_id']);
				        }
				        break;
				        case 'wz_wx':
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
				        case 'ms';
//				        include('./extend/Wxpay/appWxPayPubHelper/WxPayPubHelper.php');
//				        if($order->prepay_id){
//				       		 	
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
				        $param['notifyUrl']='https://mp.youngport.com.cn/index/Notify/ms';
				        $param['acquirerType']='wechat';
				        $param['operatorId']="小程序";
				        //查询商户的custid
				       	$mid = db::name('merchants')->where('uid',$order->user_id)->value('id');
				      	$custId = db::name('merchants_mpay')->where('uid',$mid)->value('wechat');
						
				        //$param['custId']='170615162016687';
				        add_log($custId);
				        $param['custId']=$custId;
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
				       // p($res);
      					$result=$this->httpRequst('https://aop.koolyun.com:443/apmp/rest/v2',$data,$res,'YPT17001P');
      					//p($result);
      				
      					$data = json_decode($result,true);
      					//p($data);
      					if($data['body']['responseCode']=="00"){
      						$data = json_decode($data['body']['payInfo']);
      						$prepay_id = explode('=',$data->package)[1];
      						$order->prepay_id = $prepay_id;
      						
      						$a = $order->save();
      						
      						return $data;
      					}else{
      						return $this->error($data['body']['errorMsg']);
      					}
      					
      					//return $result;
				        break;
					}
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
		//小程序签名生成
		public function create_xcx_sign($package){
				spl_autoload_unregister('think\\Loader::autoload');
				$Common_util_pub = new \Common_util_pub();
				spl_autoload_register('think\\Loader::autoload');
				$data = [];
				$data['appId'] = 'wx7aa4b28fb4fae496';
	            $data['nonceStr'] = $Common_util_pub->createNoncestr();
	            $data['package'] = 'prepay_id='.$package;
	            $data['signType'] = 'MD5';
	            $data['timeStamp'] = (string)time();
	            $data['paySign'] = $Common_util_pub->getSign($data);
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
		public function pay($out_trade_no,$transaction_id,$price,$paystyle = 1){
    				//查询订单是否支付
    				if(!$order = $this->where(array('order_sn'=>$out_trade_no,'type'=>1))->field('order_id,order_amount,mid,user_id,coupon_code,order_status,mid,card_code,integral,mobile,add_time')->find()){
    								err('没有找到订单');
    				}
    				if($order->order_status!==1){
    						err('已经支付了');
    				}
    				//开启事务
    				$this->startTrans();
    				//修改订单状态
    				$order->pay_time = time();
    				$order->order_status = 2;
    				$order->paystyle = $paystyle;
    				$order->transaction = $transaction_id;
    				$order->real_price = $price;
    				$order->save();
    					
    				$ScreenMemcardUse = model('ScreenMemcardUse');
    				
    				$card = $ScreenMemcardUse->lists($order->user_id,$order->mid);
    				//获得积分
    				if($card){
    						$card = $card[0];
    						$integral = (int)($price/$card['expense'])*$card['expense_credits'];
    						add_log($integral);
    						$integral&&$ScreenMemcardUse->updateuser($order->card_code,$integral,$out_trade_no);
    				}
    				//扣除积分
    				if($order->card_code&&$order->integral){
    						add_log(-$order->integral);
    						$ScreenMemcardUse->updateuser($order->card_code,-$order->integral,$out_trade_no);
    				}

					//查看是否存在会员卡
							    				
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
    							$pay['customer_id'] = db::name('screen_mem')->where('id',$order->mid)->value('openid');
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
    							$pay['bank'] = 2;
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
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}