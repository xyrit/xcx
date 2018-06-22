<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Store as StoreModel;
use think\Controller;
use think\Db;
use \think\View;
class Order extends Home
{
//	protected function _initialize(){
//		 parent::_initialize();
//	  	 p('store_initialize'); 	
//	}
	
	    public function index()
	    {
    	 //检测store_id is empty
    	  ($store_id = input('store_id')) || err('store_id is empty');
          //餐桌编号
          //查看购物车
          $data['cart'] = model('cart')->lists($store_id,UID,true);
          $data['goods_nums'] = $GLOBALS['cart_nums'];
    	  $data['goods_price'] = $GLOBALS['cart_price'];
    	  //查看商家设置
    	  $dc_set = model('dcset')->lists($store_id);
    	  $data['db'] = $dc_set['db'];
    	  $data['db_price'] = $dc_set['db_price'];
    	  $data['ch_price'] = $dc_set['ch_price'];
    	  $data['cj_p'] = $dc_set['ch_price'];
    	  $data['ps_price'] = $dc_set['ps_price'];
          //查看优惠劵
          $coupons = model('ScreenUserCoupons');
		  $re = $coupons->lists($store_id,UID,1);
		  if($re){
		  	$data['coupons'] = $re;
		  }else{
		  	$data['coupons'] = array();
		  }
		  
		  //查找会员
		  $card = model('ScreenMemcardUse');
		  $res = $card->lists($store_id,UID);
		  if($res){
		  	$data['card'] = $res[0];
		  	$data['cards'] = $res;
		  }else{
		  	$data['card'] = array();
		  }
		  
		  // dump($res);
          succ($data);
	    }

	    public function create_info()
	    {
    	 //检测store_id is empty
    	  ($store_id = input('store_id')) || err('store_id is empty');
          //餐桌编号
          //查看购物车
          $data['cart'] = model('cart')->lists($store_id,UID,true);
          $data['goods_nums'] = $GLOBALS['cart_nums'];
    	  $data['goods_price'] = $GLOBALS['cart_price'];
    	  //查看商家设置
    	  $dc_set = model('dcset')->lists($store_id);
    	  $data['db'] = $dc_set['db'];
    	  $data['db_price'] = $dc_set['db_price'];
    	  $data['ch_price'] = $dc_set['ch_price'];
    	  $data['cj_p'] = $dc_set['ch_price'];
    	  $data['ps_price'] = $dc_set['ps_price'];
          //查看优惠劵
          $coupons = model('ScreenUserCoupons');
		  $re = $coupons->lists($store_id,UID,1);
		  if($re){
		  	$data['coupons'] = $re;
		  }else{
		  	$data['coupons'] = array();
		  }
		  
		  //查找会员
		  $card = model('ScreenMemcardUse');
		  $res = $card->lists_two($store_id,UID);
		  if($res){
		  	$data['card'] = $res[0];
		  	$data['cards'] = $res;
		  }else{
		  	$data['card'] = array();
		  }
		  
		  // dump($res);
          succ($data);
	    }

	    //获取折扣，优惠劵，积分，余额
	    public function card_coupon_check($goods_price,$memid,$uid)
	    {
	    	dump($memid);
	    	$mem = db::name('screen_mem')->where('id',$memid)->where('delete',0)->find();
	    	$openid = $mem['unionid'];  //获取openid
	    	dump($openid);
	    	//获取商户id
	    	$mid = db::name('merchants')->where('uid',$uid)->field('id')->find();
	    	$mid = $mid['id'];
	   //  	Db::view('User','id,name')
				// ->view('Profile','truename,phone,email','Profile.user_id=User.id')
				// ->view('Score','score','Score.user_id=Profile.id')
				// ->where('score','>',80)
				// ->select();
			$where = array('fromname'=>$openid,'screen_user_coupons.status'=>1,'mid'=>$mid);
			dump($where);
	    	$coupons = Db::view('screen_user_coupons','coupon_id,card_id,status,fromname')
	    		->view('screen_coupons','id,card_id,mid,status,total_price,de_price,begin_timestamp,end_timestamp','screen_user_coupons.coupon_id=screen_coupons.id')
	    		->where($where)
	    		// ->where(time(),'between','begin_timestamp,end_timestamp')
	    		->select();
	    		echo Db::view('screen_user_coupons','coupon_id,card_id,status,fromname')
	    		->view('screen_coupons','id,card_id,mid,status,total_price,de_price,begin_timestamp,end_timestamp','screen_user_coupons.coupon_id=screen_coupons.id')
	    		->where($where)
	    		// ->where(time(),'between','begin_timestamp,end_timestamp')
	    		->getLastSql();
	    }

	  	public function lists(){
					$type = input('type');
					($store_id = input('store_id')) || err('store_id is empty');
					$order = model('order');
					if($data = $order->lists(UID,$type,$store_id)){
							succ($data);
					}else{
							err($order->getError());
					}
		}
		public function info_detail(){
					($order_id = input('order_id')) || $this->error('order_id is empty');
					$order = model('order');
					if($data = $order->info_detail($order_id,UID)){
							succ($data);
					}else{
							err($order->getError());
					}
		}

		public function create_order()
		{
			$type = input('type'); //订单类型
			($store_id = input('store_id')) || err('商家用户id为空');
			($order_amount = input('order_amount')) || err('应付款金额为空');
			$data = array(
				'type'=>$type,  
				'store_id'=>$store_id,
				'uid'=>UID,   //会员id
				'order_amount'=>$order_amount,
				'note'=>input('note'),  //用户备注
				'discount'=>input('discount'),//订单折扣
				'discount_money'=>input('discount_money'), //使用折扣抵扣
				'coupons_id'=>input('coupons_id'),   //优惠劵领取表id
				'integral'=>input('integral'),   //使用积分
				'integral_money'=>input('integral_money'),    //使用积分抵扣
				'user_money'=>input('yue'),  //使用储值
				'pack'=>input('pack',2),
				'card_code'=>input('card_code',0)
				);
			if($type==1){
				($data['no'] = input('no')) || err('餐桌号为空');	//餐桌号
				$data['tableware'] = input('tableware'); //餐具数量

			} elseif($type==2){
				($data['address_id'] = input('address_id')) || err('收货地址为空');
				$data['ps_price'] = input('ps_price'); //配送费
			}
			$addres = model('address')->info(input('address_id'));
			//判断是否可以购买 2未开业 3距离不够 4不在营业时间内 5未开启外卖
			$res = model('merchants')->check_set($store_id,$addres['lon'],$addres['lat'],$addres['area_id'],$type);
			if ($res['status']==2) {
				err('该店铺未营业!');
			}elseif($res['status']==3){
				err('收货地址不在配送范围!');
			}elseif($res['status']==4){
				err('不在营业时间内!');
			}elseif($res['status']==5){
				err('不支持外卖配送');
			}
			$order = model('order');
			//添加订单
			if($order_id = $order->add_order($data)){
				$pay = model('pay');
				//请求支付
				if($result = $pay->create_sign($order_id)){
					 succ(['order_id'=>$order_id,'data'=>$result]);
				}else{
					 err($pay->getError());
				}
				
			}else{
				 err($order->getError());
			}


		}

    	//生成点餐订单
		public function create(){	
			($store_id = input('store_id')) || err('store_id is empty');
			$note = input('note');
			//是否打包
			$pack = input('pack');
			($no = input('no')) || err('餐桌号为空');	//餐桌号
			$tableware = input('tableware'); //餐具数量
			($order_amount = input('order_amount')) || err('应付款金额为空');
			$coupons_id = input('coupons_id'); //优惠劵领取表id
			$integral = input('integral'); //使用积分
			$integral_money = input('integral_money'); //使用积分抵扣
			$discount = input('discount');  //订单折扣
			$user_money = input('yue'); //使用余额
			
			//检测商品库存
			$order = model('order');
			if($order_id = $order->add($store_id,UID,$pack,$note,$no,$tableware,$order_amount,$discount,$coupons_id,$integral,$integral_money,$user_money)){
				$pay = model('pay');
				if($data = $pay->create_sign($order_id)){
					 succ(['order_id'=>$order_id,'data'=>$data]);
				}else{
					 err($pay->getError());
				}
				
			}else{
				 err($order->getError());
			}
		}

		//生成外卖订单
		public function create_w(){	
			($store_id = input('store_id')) || err('store_id is empty');
			$note = input('note');//备注
			//是否打包
			$pack = input('pack');
			($address_id = input('address_id')) || err('收货地址为空');	
			$tableware = input('tableware'); //餐具数量
			($order_amount = input('order_amount')) || err('应付款金额为空');
			$coupons_id = input('coupons_id'); //优惠劵领取表id
			$integral = input('integral'); //使用积分
			$integral_money = input('integral_money'); //使用积分抵扣
			$discount = input('discount');  //订单折扣
			$user_money = input('yue'); //使用余额
			$ps_price = input('ps_price'); //配送费
			$yh_price = input('yh_price'); //优惠金额
			//检测商品库存
			$order = model('order');
			if($order_id = $order->add1($store_id,UID,$pack,$note,$address_id,$tableware,$order_amount,$discount,$coupons_id,$integral,$integral_money,$user_money,$ps_price,$yh_price)){
				$pay = model('pay');
				if($data = $pay->create_sign($order_id)){
					 succ(['order_id'=>$order_id,'data'=>$data]);
				}else{
					 err($pay->getError());
				}
				
			}else{
				 err($order->getError());
			}
		}
  		public function go_pay(){
				$pay = model('pay');
				//去支付
				if($data = $pay->create_sign(input('order_id'))){
					 	succ($data);
				}else{
						$this->err($pay->getError());
				}	
		}
		public function cancel(){
					($order_id = input('order_id')) || err('order_id is empty');
					$order = model('order');
					if($order->cancel($order_id,UID)){
							succ();
					}else{
							err($order->getError());
					}
		}
		public function comfirm(){
				$order = model('order');
					
				//去支付
				if($data = $order->comfirm(input('order_id'),UID)){
					 	succ($data);
				}else{
						$this->err($order->getError());
				}
		}

		
}
