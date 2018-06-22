<?php
namespace app\index\controller;
use think\Db;

class Order extends Home
{
		
		public function info_detail(){
					($order_id = input('order_id')) || $this->error('order_id is empty');
					$order = model('order');
					if($data = $order->info_detail($order_id,UID)){
							succ($data);
					}else{
							err($order->getError());
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
		public function create_info(){
				$order = [];
				($goods_id = input('goods_id')) ||err('goods_id is empty');
				($nums = input('nums'))  || err('nums is empty');
				($store_id = input('store_id'));
				($attr = input('attr'));
				
				//查看用户是否有优惠券
				
				//获取默认地址
				$address = Model('user')->_default();
				$order['address'] =$address?$address:[];
				
				$nums = explode(',',$nums);
				$attr = explode(',',$attr);
				$order['lists']  = [];
				$goods_price=0;
				$yf = 0;
				foreach(explode(',',$goods_id) as $key => $v){
					  		($data = Db::name('goods')->where('goods_id',$v)->field('goods_name,is_sku,shop_price as price,goods_img1 as picture,freight')->find()) || err('商品已经下架');
					  		// $data['picture'] = URL.$data['picture'];
					  		$picture = $data['picture'];
							if(preg_match("/\x20*https?\:\/\/.*/i",$data['picture'])){
							    $data['picture'] = $picture;
							}else{
							    $data['picture'] = URL.$picture;
							}
					  		$data['properties'] = '';
					  		if($data['is_sku']){	
					  				$attr[$key]||err('is_sku is empty');
					  				($goods_sku = Db::name('goods_sku')->where('sku_id',$attr[$key])->where('goods_id',$v)->find()) || err('该商品已经下架');
					  				
					  				$data['price'] = $goods_sku['price'];
					  				$data['properties'] = $goods_sku['properties'];
					  		}
					  		$yf = $data['freight']>$yf?$data['freight']:$yf;
					  		$goods_price += $data['price'] * $nums[$key];
					  		$data['nums'] = $nums[$key];
					  		$order['lists'][] = $data;
				}
				$price = $goods_price;
				
				//积分
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				$card_price = 0;
				if($Memcard&&$price>$Memcard[0]['credits_discount']){
					// dump($memcard);
							$Memcard = $Memcard[0];
//							while(($Memcard['card_balance']-=$Memcard['credits_use'])>=0&&($price-=$Memcard['credits_discount'])>=0){
//											$card_price+=$Memcard['credits_discount'];
//							}
							$Memcard['length'] = 1;
				}
				
				//获得优惠券
				$order['coupons'] = model('ScreenUserCoupons')->can_use($store_id,UID,$price);
				$order['yf'] = $yf;
				$order['goods_price'] = round($goods_price ,2);
				$order['price'] = round($goods_price+$yf,2);
				$order['memcard'] =$Memcard;
				succ($order);
		}
		
		public function create_info1(){
				$order = [];
				($goods_id = input('goods_id')) ||err('goods_id is empty');
				($nums = input('nums'))  || err('nums is empty');
				($store_id = input('store_id'));
				($attr = input('attr'));
				
				//查看用户是否有优惠券
				
				//获取默认地址
				$address = Model('user')->_default();
				// $address1= Model('user')->address_update
				$order['address'] =$address?$address:[];
				
				$nums = explode(',',$nums);
				$attr = explode(',',$attr);
				$order['lists']  = [];
				$goods_price=0;
				foreach(explode(',',$goods_id) as $key => $v){
					  		($data = Db::name('goods')->where('goods_id',$v)->field('goods_name,is_sku,shop_price as price,goods_img1 as picture,freight')->find()) || err('商品已经下架');
					  		$data['picture'] = URL.$data['picture'];
					  		$data['properties'] = '';
					  		if($data['is_sku']){	
					  				$attr[$key]||err('is_sku is empty');
					  				($goods_sku = Db::name('goods_sku')->where('sku_id',$attr[$key])->where('goods_id',$v)->find()) || err('该商品已经下架');
					  				
					  				$data['price'] = $goods_sku['price'];
					  				$data['properties'] = $goods_sku['properties'];
					  		}
					  		$goods_price += $data['price'] * $nums[$key];
					  		$data['nums'] = $nums[$key];
					  		$order['lists'][] = $data;
				}
				$price = $goods_price;
				
				//积分
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				$card_price = 0;
				if($Memcard&&$price>$Memcard[0]['credits_discount']){
					$Memcard = $Memcard[0];
					if ($Memcard['level_set']==1) {
						$discount = Db::name('screen_memcard_level')->where(array('c_id'=>$Memcard['id'],'level'=>$Memcard['level']))->find();
							$Memcard['discount'] = $discount['level_discount'];
					}
					$Memcard['length'] = 1;
				}
				
				//获得优惠券
				$order['coupons'] = model('ScreenUserCoupons')->can_use($store_id,UID,$price);
				$order['goods_price'] = round($goods_price ,2);
				$order['price'] = round($goods_price,2);
				$order['memcard'] =$Memcard;
				succ($order);
		}

		public function lists(){
				($store_id = input('store_id')) || err('store_id is empty');
				$type = input('type');
				$order = model('order');
				
				if($data = $order->lists($store_id,UID,$type,input('page'))){
						succ($data);
				}else{
						err($order->getError());
				}
		}


		
		//生成订单
		public function create(){
			// err('系统维护中');
					//($store_id = input('store_id')) ||err('store_id is empty');
					($goods_id = input('goods_id')) ||err('goods_id is empty');
					($nums = input('nums'))  || err('nums is empty');
					$attr = input('attr');
					($store_id = input('store_id')) || err('store_id is empty');
					($address_id = input('address_id')) || err('请填写收货地址!');
					$coupons_id = input('coupons_id',0);
					$staff_id = input('staff_id',0);
					$address = model('address');
					$addres = $address->info($address_id);
					$res = model('merchants')->check_store($store_id,$addres['lon'],$addres['lat'],$addres['area_id']);
					if ($res==3) {
						err('收货地址不在配送范围!');
					}
					//检测商品库存
					$order = model('order');
					if($order_id = $order->add($goods_id,$nums,$attr,$address_id,$store_id,$coupons_id,$staff_id)){
						 //生成签名
						 if($data = $order->create_sign($order_id,'ms')){
						 	
						 			succ($data,'succ',['order_id'=>$order_id]);
						 }else{
						 		err($order->getError());
						 }
					}else{
						 err($order->getError());
					}
		}
		//生成订单
		public function create1(){
					//($store_id = input('store_id')) ||err('store_id is empty');
					($goods_id = input('goods_id')) ||err('goods_id is empty');
					($nums = input('nums'))  || err('nums is empty');
					$attr = input('attr');
					($store_id = input('store_id')) || err('store_id is empty');
					($address_id = input('address_id')) || err('请填写收货地址!');
					$coupons_id = input('coupons_id',0);
					$staff_id = input('staff_id',0);
				
					//检测商品库存
					$order = model('order');
					if($order_id = $order->add($goods_id,$nums,$attr,$address_id,$store_id,$coupons_id,$staff_id)){
						 //生成签名
						 if($data = $order->create_sign($order_id,'ms')){
						 	
						 			succ($data,'succ',['order_id'=>$order_id]);
						 }else{
						 		err($order->getError());
						 }
					}else{
						 err($order->getError());
					}
		}
		public function pay(){
					($order_id = input('order_id')) ||err('order_id is empty');
					$order_info = model('order')->info($order_id,UID);
					empty($order_info) && $this->err('不存在该订单');
					$order_info['order_state']==10?$this->succ($order_info):$this->err('该订单已经支付');
		}
		public function go_pay(){
				$order = model('order');
				//去支付
				if($data = $order->create_sign(input('order_id'),input('pay_way'))){
					 	succ($data);
				}else{
						$this->err($order->getError());
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
		//评价
		public function pj(){
				$pj = model('Pj');
				($content = input('pj/a')) ||err('评价内容为空');
				($order_id = input('order_id')) ||err('order_id is empty');
				if($pj->add($order_id,UID,$content)){
					succ();
				}else{
					err($pj->getError());
				}
				
				
		}
		//支付成功
		public function succs(){
				($store_id = input('store_id')) || err('store_id is emptu');
				$order_id = input('order_id');
				$res = [];
  				//查询是否领取
  				
  				$Memcard = model('ScreenMemcardUse');
				$data = $Memcard->lists($store_id,UID);
				if(empty($data)){
					
					$Memcard = model('Memcard');
					if ($order_id) {
						if($Memcard->check($store_id,$order_id)){
							$data = $Memcard->lists($store_id,UID);			
  							$res['Memcard'] = $data?$data:[];
						}else{
							$res['Memcard'] = [];
						}

					}else{
						$data = $Memcard->lists($store_id,UID);			
  						$res['Memcard'] = $data?$data:[];
  					}
				}else{
					$res['Memcard'] = [];
				}
				//查询热门商品
				$res['hot'] = model('goods')->hot($store_id);
				succ($res);
		}

		/**
		 * 新版生成订单
		 * @return [type] [description]
		 */
		public function create_new(){
					//($store_id = input('store_id')) ||err('store_id is empty');
					($goods_id = input('goods_id')) ||err('goods_id is empty');
					($nums = input('nums'))  || err('nums is empty');
					$attr = input('attr');
					($store_id = input('store_id')) || err('store_id is empty');
					($address_id = input('address_id')) || err('请填写收货地址!');
					$coupons_id = input('coupons_id',0);
					$staff_id = input('staff_id',0);
					$shipping_ps = input('shipping_ps',0);
					$discount_price = input('discount_price',0);
					$integral_price = input('integral_price',0);
					$balance_price = input('balance_price',0);
					$discount = input('discount',10);
					$card_code = input('card_code',0);
					$yh_integral = input('yh_integral',0);
					$address = model('address');
					$addres = $address->info($address_id);
					// dump($store_id);dump($addres['lon']);dump($addres['lat']);dump($addres['area_id']);
					$location = $addres['address'].$addres['addresses'];
					$check = $this->check_water($goods_id,$location);
					if(!$check){
						err('没有购买权限!');
					}
					$res = model('merchants')->check_set($store_id,$addres['lon'],$addres['lat'],$addres['area_id']);
					if ($res['status']==3) {
						err('收货地址不在配送范围!');
					}
					//检测商品库存
					$order = model('order');
					if($order_id = $order->add_new($goods_id,$nums,$attr,$address_id,$store_id,$coupons_id,$staff_id,$shipping_ps,$discount_price,$integral_price,$balance_price,$discount,$card_code,$yh_integral)){
						 //生成签名
						 if($data = $order->create_sign($order_id,'ms')){
						 	
						 			succ($data,'succ',['order_id'=>$order_id]);
						 }else{
						 		err($order->getError());
						 }
					}else{
						 err($order->getError());
					}
		}

		/**
		 * app 1.7 后新版确认订单
		 * @return [type] [description]
		 */
		public function create_info_new(){
				$order = [];
				($goods_id = input('goods_id')) ||err('goods_id is empty');
				($nums = input('nums'))  || err('nums is empty');
				($store_id = input('store_id'));
				($attr = input('attr'));
				
				//查看用户是否有优惠券
				
				//获取默认地址
				$address = Model('user')->_default();
				// $address1= Model('user')->address_update
				$order['address'] =$address?$address:[];
				
				$nums = explode(',',$nums);
				$attr = explode(',',$attr);
				$order['lists']  = [];
				$goods_price=0;
				foreach(explode(',',$goods_id) as $key => $v){
					  		($data = Db::name('goods')->where('goods_id',$v)->field('goods_name,is_sku,shop_price as price,goods_img1')->find()) || err('商品已经下架');
							$picture = $data['goods_img1'];
							if(preg_match("/\x20*https?\:\/\/.*/i",$data['goods_img1'])){
							    $data['picture'] = $picture;
							}else{ 
							    $data['picture'] = URL.$picture;
							}
					  		// $data['picture'] = URL.$data['picture'];
					  		$data['properties'] = '';
					  		if($data['is_sku']){	
					  				$attr[$key]||err('is_sku is empty');
					  				($goods_sku = Db::name('goods_sku')->where('sku_id',$attr[$key])->where('goods_id',$v)->find()) || err('该商品已经下架');
					  				
					  				$data['price'] = $goods_sku['price'];
					  				$data['properties'] = $goods_sku['properties'];
					  		}
					  		$goods_price += $data['price'] * $nums[$key];
					  		$data['nums'] = $nums[$key];
					  		$order['lists'][] = $data;
				}
				$price = $goods_price;
				
				//积分
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				$card_price = 0;
				if($Memcard){
					$Memcard = $Memcard[0];
					if ($Memcard['level_set']==1) {
						$discount = Db::name('screen_memcard_level')->where(array('c_id'=>$Memcard['id'],'level'=>$Memcard['level']))->find();
							$Memcard['discount'] = $discount['level_discount'];
					}
					$Memcard['length'] = 1;
				}
				
				//获得优惠券
				$order['coupons'] = model('ScreenUserCoupons')->can_use($store_id,UID,$price);
				$order['goods_price'] = round($goods_price ,2);
				$order['price'] = round($goods_price,2);
				$order['memcard'] =$Memcard;
				succ($order);
		}

		/**
		 * app 增加异业联盟卡 确认订单
		 * @return [type] [description]
		 */
		public function create_info_two()
		{
			$order = [];
			($goods_id = input('goods_id')) ||err('goods_id is empty');
			($nums = input('nums'))  || err('nums is empty');
			($store_id = input('store_id'));
			($attr = input('attr'));
			
			//获取默认地址
			$address = Model('user')->_default();
			$order['address'] =$address?$address:[];
			
			$nums = explode(',',$nums);
			$attr = explode(',',$attr);
			$order['lists']  = [];
			$goods_price=0;
			foreach(explode(',',$goods_id) as $key => $v){
		  		($data = Db::name('goods')->where('goods_id',$v)->field('goods_name,is_sku,shop_price as price,goods_img1')->find()) || err('商品已经下架');
				$picture = $data['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$data['goods_img1'])){
				    $data['picture'] = $picture;
				}else{ 
				    $data['picture'] = URL.$picture;
				}
		  		$data['properties'] = '';
		  		if($data['is_sku']){
	  				$attr[$key]||err('is_sku is empty');
	  				($goods_sku = Db::name('goods_sku')->where('sku_id',$attr[$key])->where('goods_id',$v)->find()) || err('该商品已经下架');
	  				$data['price'] = $goods_sku['price'];
	  				$data['properties'] = $goods_sku['properties'];
		  		}
		  		$goods_price += $data['price'] * $nums[$key];
		  		$data['nums'] = $nums[$key];
		  		$order['lists'][] = $data;
			}
			$price = $goods_price;
			
			//积分
			$Memcard = model('ScreenMemcardUse')->lists_two($store_id,UID);
			if($Memcard){
				foreach ($Memcard as $key => $value) {
					if ($value['level_set']==1) {
					$discount = Db::name('screen_memcard_level')->where(array('c_id'=>$value['id'],'level'=>$value['level']))->find();
						$value['discount'] = $discount['level_discount'];
					}
				}
				$order['memcard'] =$Memcard[0];
			}
			
			//获得优惠券 查看用户是否有优惠券
			$order['coupons'] = model('ScreenUserCoupons')->can_use($store_id,UID,$price);
			$order['goods_price'] = round($goods_price ,2);
			$order['price'] = round($goods_price,2);
			$order['memcards'] =$Memcard;
			succ($order);
		}

		/**
		 * app 1.7 后新版支付成功
		 * @return [type] [description]
		 */
		public function succs_new(){
				($store_id = input('store_id')) || err('store_id is emptu');
				$order_id = input('order_id');
				$res = [];
  				//查询是否领取
  				
  				$Memcard = model('ScreenMemcardUse');
				$data = $Memcard->lists($store_id,UID);
				if(empty($data)){
					
					$Memcard = model('Memcard');
					if ($order_id) {
						if($Memcard->check($store_id,$order_id)){
							$data = $Memcard->lists($store_id,UID);			
  							$res['Memcard'] = $data?$data:[];
						}else{
							$res['Memcard'] = [];
						}

					}else{
						$data = $Memcard->lists($store_id,UID);			
  						$res['Memcard'] = $data?$data:[];
  					}
				}else{
					$res['Memcard'] = [];
				}
				//查询热门商品
				$res['hot'] = model('product')->hot($store_id);
				succ($res);
		}

		public function check_water($goods_id,$location){
			if ($goods_id==6124) {
				//'ser_id=15 and transaction <> '' and total_amount in (99,168)'
				$where['user_id'] =15; 
				$where['p.status'] =1; 
				$where['transaction'] = array('neq','');
				$where['total_amount'] = array('in','99,168');
				$order = Db::name('order')->alias('o')->join('pay p','o.order_sn = p.remark')->where($where)->field('o.order_id,o.order_sn,o.mid,o.user_id,o.order_status,o.total_amount,o.consignee,o.address,o.mobile,o.type,o.prepay_id,o.transaction,o.real_price,o.pay_time,o.update_time,o.staff_id,p.status')->select();
				foreach ($order as $key => $value) {
					if($value['address']==$location){
						return true;
					}
				}
				return false;
			}
			return true;
		}
}
