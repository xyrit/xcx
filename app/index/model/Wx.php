<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Wx extends Model
{			
			public static $appid = 'wx3fa82ee7deaa4a21';
   			public static $secret = '6b6a7b6994c220b5d2484e7735c0605a';
			//获得token
			public function get_token(){
							$token = db::name('weixin_token')->where('type',1)->find();
							$time = time();
			    			if(empty($token) || $token['a_time']+5000<$time){
			    						//获取token
			    						$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$appid.'&secret='.self::$secret;
			    						$token = curl_post($url,[]);
			    						$token = json_decode($token,true)['access_token'];
			    						$token&&db::name('weixin_token')->where('type','1')->update(['access_token'=>$token,'a_time'=>$time]);
			    			}else{
			    				$token = $token['access_token']; 
			    			}
			    			return $token;
			}
			public function get_xcx_token($mid=0){
						
							//查看store_id
							$appid = db::name('appid')->where('mid',$mid)->find();
							
							$token1 = db::name('config')->where('name','xcx_access_token')->where('type',$mid)->find();
							
							$time = time();
			    			if(empty($token1) || $token1['add_time']+7200<$time){
			    								//获取token
			    						$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid['appid'].'&secret='.$appid['secret'];
			    						$token = curl_post($url,array());
			    						var_dump($token);
			    						$token = json_decode($token,true)['access_token'];
			    						$token1?db::name('config')->where('name','xcx_access_token')->where('type',$mid)->update(['value'=>$token,'add_time'=>$time]):db::name('config')->insert(['name'=>'xcx_access_token','value'=>$token,'add_time'=>$time,'type'=>$mid]);
			    						
			    			}else{
			    						$token = $token1['value']; 
			    			}
			    			return $token;
			}
			//发送消息模版
			public function moban($type,$order_id){
						//查询订单信息
						$order = db::name('order')->where('order_id',$order_id)->find();
					
						if(empty($order)){
								return $this->error('order is empty');
						}
						//查询用户的openid
						$mem = db::name('screen_mem')->where('id',$order['mid'])->find();
						$openid = $mem['openid'];
						$pid = $mem['userid'];
						//$pid = db::name('merchants_users')->where('id',$order['user_id'])->value('pid');
						$appid = db::name('appid')->where('uid',$pid)->find();
						$userid = db::name('merchants_users')->where('id',$order['user_id'])->find();
						//发货
						switch($type){
								case 'fahuo':
								if($order['order_status']!=2){
										return $this->error('该订单不能发货');
								}
								$mid = $appid['fahuo'];
								$keyword['keyword1'] =["value"=>$userid['user_phone'],"color"=>"#173177"];
								$keyword['keyword2'] =["value"=>date('Y-m-d H:i:s',$order['add_time']),"color"=>"#173177"];
								$keyword['keyword3'] =["value"=>$order['order_sn'],"color"=>"#173177"];
								$keyword['keyword4'] =["value"=>$order['address'],"color"=>"#173177"];
								$keyword['keyword5'] =["value"=>date('Y-m-d H:i:s',$order['add_time']),"color"=>"#173177"];
								$keyword['keyword6'] =["value"=>$order['consignee'],"color"=>"#173177"];
								$keyword['keyword7'] =["value"=>$order['mobile'],"color"=>"#173177"];
							
								break;
								case 'pay':
								if($order['order_status']!=2){
									return $this->error('该订单没有付款');
								}
								$mid = $appid['topay'];
									$keyword['keyword1'] =["value"=>$order['order_sn'],"color"=>"#173177"];
									$keyword['keyword2'] =["value"=>$order['real_price'],"color"=>"#173177"];
									$keyword['keyword3'] =["value"=>date('Y-m-d H:i:s',$order['add_time']),"color"=>"#173177"];
									$keyword['keyword4'] =["value"=>$order['order_amount'],"color"=>"#173177"];
									$keyword['keyword5'] =["value"=>'微信支付',"color"=>"#173177"];
									$keyword['keyword6'] =["value"=>$order['address'],"color"=>"#173177"];
									$name = db::name('merchants')->where('uid',$order['user_id'])->value('merchant_name');
									$keyword['keyword7'] =["value"=>$name,"color"=>"#173177"];
								break;
						}
						$token  = $this->get_xcx_token($appid['mid']);
						$param = [];
						$param['touser'] = $openid;
						$param['template_id'] = $mid;
						$param['form_id'] = $order['prepay_id'];
						$param['data'] = $keyword;
						p($param);
						$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$token;
						$data  =curl_post1($url,json_encode($param));
						p($data);
						
			}
			public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  		}
	    
	  	
}