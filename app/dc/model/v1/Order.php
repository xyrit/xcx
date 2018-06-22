<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
use app\dc\model\v1\OrderGoods;
class Order extends Model
{
//		0:取消
//		10:默认
//		20:已经付款
//		30:已经发货了
//		40:交易完成
// 		50:退货
		protected $createTime = 'add_time';
		protected $updateTime = 'update_time';
		public function lists($uid,$type,$store_id){
						$where['user_id'] = $store_id;
						$where['mid'] = $uid;
						$where['type'] = 2;
						switch($type){	
							case 1:				//代付款
							$where['order_status'] = 1;
							break;
							case 2:
							$where['order_status'] = 2;
							break;
							case 3:			//已发货
							$where['order_status'] = 3;
							break;
							case 5:			//已付款的
							$where['order_status'] = 5;
							break;
							default:
							break;
						}
						$data = $this->where($where)->order('update_time desc')->paginate(10);
						foreach($data as $key=>$v){
							$uid = $v['user_id'];
							$order_id = $v['order_id'];
							$mid = db('merchants')->where('uid',$uid)->field('merchant_name')->find();
							$data[$key]['merchant_name'] = $mid['merchant_name'];
							// dump($order_id);
							$order_good = db('order_goods')->where('order_id',$order_id)->field('goods_name,goods_num,goods_price,spec_key')->find();
							$data[$key]['goods_name'] = $order_good['goods_name'];
							$data[$key]['goods_num'] = $order_good['goods_num'];
							$data[$key]['goods_price'] = $order_good['goods_price'];
							$s = db('goods_sku')->where('sku_id',$order_good['spec_key'])->field('properties')->find();
							$data[$key]['properties'] = $s['properties'];
							$order_goods = db('order_goods')->where('order_id',$order_id)->field('goods_name,goods_num,goods_price,spec_key')->select();
							$total = 0;
							if ($order_goods) {
								foreach ($order_goods as $k => $value) {

									$total += $order_goods[$k]['goods_num'];
									$goods = array();
									if ($order_goods[$k]['spec_key']) {
										$sku = db('goods_sku')->where('sku_id',$order_goods[$k]['spec_key'])->field('properties')->find();
										$goods['properties'] = $sku['properties'];
									}
									
									$goods['goods_name'] = $order_goods[$k]['goods_name'];
									$goods['goods_num'] = $order_goods[$k]['goods_num'];
									$goods['goods_price'] = $order_goods[$k]['goods_price'];
									$good[$k] = $goods;
								}
								$data[$key]['goods'] = $good;
								unset($good);
							}else{
								$data[$key]['goods'] = [];
							}
							
							$data[$key]['total'] = $total;
							
							$data[$key]['openPicker'] = 0;
							// dump($data[$key]['goods']);
							$no = db('dc_no')->where(array('id'=>$v['dc_no']))->field('no')->find();
							if ($no) {
								$data[$key]['no'] = $no['no'];
							}else{
								$data[$key]['no'] = 0;
							}
								switch((int)$v['order_status']){
										case 0:
										$data[$key]['order_status_name'] = '已取消';
										break;
										case 1;
										$data[$key]['order_status_name'] = '待付款';
										break;
										case 2:
										$data[$key]['order_status_name'] = '待发货';
										break;
										case 3:
										$data[$key]['order_status_name'] = '已发货';
										break;
										case 4:
										$data[$key]['order_status_name'] = '已收货';
										break;
										case 5:
										$data[$key]['order_status_name'] = '已付款';
										break;
								}
							//	$data[$key]->add_time = date('Y-m-d H:i:s',$data[$key]->add_time);
						}
						// dump($data);
						return $data;
		}
		public function info_detail($order_id,$uid,$field=true){
				if(!$order = $this->where('order_id',$order_id)->where('mid',$uid)->field($field)->find()){
							return $this->error('没有该订单');
				}
				$uid = $order['user_id'];
				$merchant_name = db('merchants')->where('uid',$uid)->field('merchant_name')->find();
				$order['merchant_name'] = $merchant_name['merchant_name'];
				$goods = db::name('order_goods')->where('order_id',$order_id)->select();
				foreach($goods as $key => &$v){
						$v['goods_img'] = URL.$v['goods_img'];
				}
				$order->goods = $goods; 
				return $order;
		}

		/**
		 * app 1.7 添加订单
		 */
		public function add_order($data)
		{
			$goods = model('product');
			$order_goods = $order_good =  [];
			$goods_price = 0;
			$order_price = 0;
			$order_goods_num = 0;
			//查询购物车
			$cart = Db::name('dc_cart')->where('store_id',$data['store_id'])->where('uid',$data['uid'])->where('status',1)->select();
			//查询收货地址
			if($data['type']==2){
				if(!$address = db::name('address')->where('id',$data['address_id'])->where('uid',$data['uid'])->find()){
					return $this->error('该收货地址不存在');
				}
			}else{
				//查询餐桌
				$id = db('merchants')->where('uid',$data['store_id'])->field('id')->find();
				$no_id = db('dc_no')->where(array('no'=>$data['no'],'mid'=>$id['id']))->find();
				if (!$no_id) {
					return $this->error('参数错误');
				}
			}
			$this->startTrans();
			foreach($cart as $v){
				$goods_info = $goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
				if($goods_info==false){
					return $this->error($v['goods_name'].'库存不足');
				}
				$order_good['goods_id'] = $v['goods_id'];
				$order_good['goods_name'] = $v['goods_name'];
				$order_good['goods_price'] = $goods_info['shop_price'];
				$order_good['spec_key'] = $v['attr_id'];
				$order_good['spec_key_name'] =  isset($goods_info['properties'])?$goods_info['properties']:'';
				$order_good['goods_num'] = $v['nums'];
				$order_goods_num += $v['nums'];
				$order_goods[] = $order_good;
				$goods_price += $v['nums']*$goods_info['shop_price'];
				unset($v['attr_id']);
			}
			$order['coupon_price']=0;
			//查看优惠券是否满足要求
			if($data['coupons_id']>0){
				//discount_price  折扣后金额
				if ($data['discount']) {
					$discount_price = ($data['discount']/10)*$goods_price;
				}else{
					$discount_price  = $goods_price;
				}
				$ScreenUserCoupons = model('ScreenUserCoupons');
				if(($Coupons = $ScreenUserCoupons->check($data['coupons_id'],$data['uid'],$data['store_id'],$discount_price))===false){
					return $this->error($ScreenUserCoupons->getError());
				}
				$order['coupon_price'] = $Coupons['price'];
				$order['coupon_code'] = $Coupons['usercard'];
			}
			$order['coupon_code'] = isset($Coupons)?$Coupons['usercard']:'';
			//会员卡
			if ($data['card_code']) {
				$order['card_code'] = $data['card_code'];
				$memcard_id =db::name('screen_memcard_use')->where('card_code',$data['card_code'])->value('memcard_id');
				$Memcard = db::name('screen_memcard')->where('id',$memcard_id)->select();
				// dump($Memcard);
			}else{
				$Memcard = model('ScreenMemcardUse')->lists($data['store_id'],$data['uid']);
				if($Memcard){
					$card_code = $Memcard[0]['card_code'];
					$order['card_code'] = $card_code;
				}
			}
			// 折扣
			if ($Memcard&&$Memcard[0]['discount_set'] == 1) {
				if ($Memcard[0]['discount'] == $data['discount']) {
					$data['discount'] = $data['discount']*10;
					$order['discount'] = $data['discount'];
					$order['discount_money'] = $data['discount_money'];
				}
			}
			// 积分
			if ($Memcard&&$Memcard[0]['integral_dikou'] == 1) {
				$order['integral'] = $data['integral'];
				$order['integral_money'] = $data['integral_money'];
			} 
			// 余额
			if ($Memcard&&$Memcard[0]['balance_set'] == 1) {
				$order['user_money'] = $data['user_money'];
			}
			//打包方式
			if($data['pack'] == 1){
				$mid = $this->_get_merchants($data['store_id']);
				$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
				$goods_price += $dc_set['db_price'];
				$order['dc_db_price'] = $dc_set['db_price'];
				$order['dc_db'] = 1;
			}elseif($data['pack'] == 2){
				$mid = $this->_get_merchants($data['store_id']);
				$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
				$goods_price += $dc_set['ch_price']*$data['tableware'];
				$order['dc_ch_price'] = $dc_set['ch_price']*$data['tableware'];
			}elseif($data['pack'] == 3){
				$mid = $this->_get_merchants($data['store_id']);
				$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
				$goods_price += $dc_set['db_price'];
				$goods_price += $dc_set['ps_price'];
				$order['dc_db_price'] = $dc_set['db_price'];
				$order['dc_ps_price'] = $dc_set['ps_price'];
				$order['dc_db'] = 3;
			}
			if ($data['type']==1) {
				$order['dc_no'] = $no_id['id'];
			}elseif($data['type']==2){
				$order['consignee'] = $address['name'];
				$order['mobile'] = $address['tel'];
				$order['address'] = $address['address'].$address['addresses'];
				$order['area_id']  =$address['area_id'];
				$order['dc_ps_price'] = $data['ps_price'];
			}
			$order_benefit = $data['discount_money'] + $order['coupon_price'] +$data['integral_money'];
			$order['order_goods_num'] = $order_goods_num;
			$order['user_note'] = $data['note'];
			$order['total_amount'] = $goods_price;
			$order['order_amount'] = $data['order_amount'];
			$order['order_benefit'] = $order_benefit;
			$order['order_sn'] = 'dc'.date('Ymdhis').rand(100000,999999);
			$order['mid'] = $data['uid'];
			$order['user_id'] = $data['store_id'];
			$order['order_status'] = 1;
			$order['paystyle'] = 1;
			$order['type'] = 2;
			if(!$this->save($order)){
				$this->rollback();
				return $this->error('添加订单失败');
			}
			$order_id = $this->getLastInsID();
			
			foreach($order_goods as &$v){
				$v['order_id'] = $order_id;
			}
			$order_goods1 = $order_goods;
			$ordergood = new OrderGoods;
			if(!$ordergood->saveAll($order_goods)){
				$this->rollback();
				return $this->error('添加商品失败');
			}
			//减去商品库存
			foreach($order_goods1 as $v){
				if(!db::name('goods')->where('goods_id',$v['goods_id'])->setDec('goods_number',$v['goods_num'])){
					$this->rollback();
					return $this->error('修改商品库存失败1');
				}
				if($v['spec_key']){
					if(!db::name('goods_sku')->where('sku_id',$v['spec_key'])->setDec('quantity',$v['goods_num'])){
							$this->rollback();
							return $this->error('修改商品库存失败');
					}
				}
				
			}
			$this->commit();
			return $order_id;
		}

		public function add($store_id,$uid,$pack=2,$note='',$no=0,$tableware,$order_amount,$discount,$coupons_id,$integral,$integral_money,$user_money){
				//$this->add();	
				//查询购物车
				$cart = Db::name('dc_cart')->where('store_id',$store_id)->where('uid',$uid)->where('status',1)->select();
				$goods = model('goods');
				$order_goods = $order_good =  [];
				$goods_price = 0;
				$order_price = 0;
				$order_goods_num = 0;
				$this->startTrans();
				foreach($cart as $v){
						$goods_info = $goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
						if($goods_info==false){
								return $this->error($v['goods_name'].'库存不足');
						}
						$order_good['goods_id'] = $v['goods_id'];
						$order_good['goods_name'] = $v['goods_name'];
						$order_good['goods_price'] = $goods_info['shop_price'];
						$order_good['spec_key'] = $v['attr_id'];
						$order_good['spec_key_name'] =  isset($goods_info['properties'])?$goods_info['properties']:'';
						$order_good['goods_num'] = $v['nums'];
						$order_goods_num += $v['nums'];
						$order_goods[] = $order_good;
						$goods_price += $v['nums']*$goods_info['shop_price'];
						//开始删除库存
		    			//删除购物车
		    			if(!db::name('goods')->where('goods_id',$v['goods_id'])->setDec('goods_number',$v['nums'])){
							$this->rollback();
							return $this->error('修改商品库存失败');
		    			}
		    			if($v['attr_id']){
							if(!db::name('goods_sku')->where('sku_id',$v['attr_id'])->setDec('quantity',$v['nums'])){
								$this->rollback();
								return $this->error('修改商品库存失败');
							}
		    			}
		    			// if(!Db::name('dc_cart')->where('store_id',$store_id)->where('uid',$uid)->where('status',1)->delete()){
		    			// 	$this->rollback();
		    			// 	return $this->error('清空购物车失败');
		    			// }
		    			unset($v['attr_id']);
				}
				//查看优惠券是否满足要求
				if($coupons_id>0){
					if ($discount) {
						$discount_price = ($discount/10)*$goods_price;
					}else{
						$discount_price  = $goods_price;
					}
					$ScreenUserCoupons = model('ScreenUserCoupons');
					if(($Coupons = $ScreenUserCoupons->check($coupons_id,UID,$store_id,$discount_price))===false){
								return $this->error($ScreenUserCoupons->getError());
					}
					$order['coupon_price'] = $Coupons['price'];
					$order['coupon_code'] = $Coupons['usercard'];
				}
				$order['coupon_code'] = isset($Coupons)?$Coupons['usercard']:'';
				//会员卡
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				if($Memcard){
					$card_code = $Memcard[0]['card_code'];
					$order['card_code'] = $card_code;
				}
				// $card_balance = $card_price = $card_code = 0;
				// $price = $order['order_amount'];
				// if($Memcard&&$price>=$Memcard[0]['credits_discount']){
				// 			$Memcard = $Memcard[0];
				// 			while(($Memcard['card_balance']-=$Memcard['credits_use'])>=0&&($price-=$Memcard['credits_discount'])>=0){
				// 							$card_price+=$Memcard['credits_discount'];
				// 							$card_balance+=$Memcard['credits_use'];
				// 			}
				// 			$card_code = $Memcard['card_code'];
				// }
				// 折扣
				if ($Memcard&&$Memcard[0]['discount_set'] == 1) {
					if ($Memcard[0]['discount'] == $discount) {
						$discount = $discount*10;
						$order['discount'] = $discount;
					}
				}
				// 积分
				if ($Memcard&&$Memcard[0]['integral_dikou'] == 1) {
					$order['integral'] = $integral;
					$order['integral_money'] = $integral_money;
				} 
				// 余额
				if ($Memcard&&$Memcard[0]['balance_set'] == 1) {
					$order['user_money'] = $user_money;
				}

				$id = db('merchants')->where('uid',$store_id)->field('id')->find();
				$no_id = db('dc_no')->where(array('no'=>$no,'mid'=>$id['id']))->find();
				if (!$no_id) {
					return $this->error('参数错误');
				}
				if($pack == 1){
					$mid = $this->_get_merchants($store_id);
					$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
					$goods_price += $dc_set['db_price'];
					$order['dc_db_price'] = $dc_set['db_price'];
					$order['dc_db'] = 1;
				}else{
					$mid = $this->_get_merchants($store_id);
					$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
					$goods_price += $dc_set['ch_price']*$tableware;
					$order['dc_ch_price'] = $dc_set['ch_price']*$tableware;
				}
				// echo db('dc_no')->getLastSql();
				//开始插入数据
				//p($order_goods);
				// dump($order_goods_num);
				
				$order_benefit = $goods_price - $order_amount;
				
				$order['dc_no'] = $no_id['id'];
				$order['order_goods_num'] = $order_goods_num;
				$order['user_note'] = $note;
				$order['total_amount'] = $goods_price;
				$order['order_amount'] = $order_amount;
				$order['order_benefit'] = $order_benefit;
				$order['order_sn'] = 'dc'.date('Ymdhis').rand(100000,999999);
				$order['mid'] = $uid;
				$order['user_id'] = $store_id;
				$order['order_status'] = 1;
				$order['paystyle'] = 1;
				$order['type'] = 2;
				// dump($order);die;
				if(!$this->save($order)){
    					$this->rollback();
    					return $this->error('添加订单失败');
    			}
				$order_id = $this->getLastInsID();
				foreach($order_goods as &$v){
						$v['order_id'] = $order_id;
				}
				if(!db::name('order_goods')->insertAll($order_goods)){
    					$this->rollback();
    					return $this->error('添加商品失败');
    			}
    			$this->commit();
    			return $order_id;
		}

		public function add1($store_id,$uid,$pack=2,$note='',$address_id=0,$tableware,$order_amount,$discount,$coupons_id,$integral,$integral_money,$user_money,$ps_price,$yh_price){
				//$this->add();	
				//查询购物车
				$cart = Db::name('dc_cart')->where('store_id',$store_id)->where('uid',$uid)->where('status',1)->select();
				$goods = model('goods');
				$order_goods = $order_good =  [];
				$goods_price = 0;
				$order_price = 0;
				$order_goods_num = 0;
				//查询收货地址
				if(!$address = db::name('address')->where('id',$address_id)->where('uid',UID)->find()){
							return $this->error('该收货地址不存在');
				}
				$this->startTrans();
				foreach($cart as $v){
						$goods_info = $goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
						if($goods_info==false){
								return $this->error($v['goods_name'].'库存不足');
						}
						$order_good['goods_id'] = $v['goods_id'];
						$order_good['goods_name'] = $v['goods_name'];
						$order_good['goods_price'] = $goods_info['shop_price'];
						$order_good['spec_key'] = $v['attr_id'];
						$order_good['spec_key_name'] =  isset($goods_info['properties'])?$goods_info['properties']:'';
						$order_good['goods_num'] = $v['nums'];
						$order_goods_num += $v['nums'];
						$order_goods[] = $order_good;
						$goods_price += $v['nums']*$goods_info['shop_price'];
						//开始删除库存
		    			//删除购物车
		    			if(!db::name('goods')->where('goods_id',$v['goods_id'])->setDec('goods_number',$v['nums'])){
		    							$this->rollback();
		    							return $this->error('修改商品库存失败');
		    			}
		    			if($v['attr_id']){
		    							if(!db::name('goods_sku')->where('sku_id',$v['attr_id'])->setDec('quantity',$v['nums'])){
		    									$this->rollback();
		    									return $this->error('修改商品库存失败');
		    							}
		    			}
		    			// if(!Db::name('dc_cart')->where('store_id',$store_id)->where('uid',$uid)->where('status',1)->delete()){
		    			// 	// echo Db::name('dc_cart')->getLastSql();
		    			// 	$this->rollback();
		    			// 	return $this->error('清空购物车失败');
		    			// }
		    			unset($v['attr_id']);
				}
				//查看优惠券是否满足要求
				if($coupons_id>0){
					if ($discount) {
						$discount_price = ($discount/10)*$goods_price;
					}else{
						$discount_price  = $goods_price;
					}
					$ScreenUserCoupons = model('ScreenUserCoupons');
					if(($Coupons = $ScreenUserCoupons->check($coupons_id,UID,$store_id,$discount_price))===false){
								return $this->error($ScreenUserCoupons->getError());
					}
					$order['coupon_price'] = $Coupons['price'];
					$order['coupon_code'] = $Coupons['usercard'];
				}
				$order['coupon_code'] = isset($Coupons)?$Coupons['usercard']:'';
				//会员卡
				$Memcard = model('ScreenMemcardUse')->lists($store_id,UID);
				if($Memcard){
					$card_code = $Memcard[0]['card_code'];
					$order['card_code'] = $card_code;
				}
				// $card_balance = $card_price = $card_code = 0;
				// $price = $order['order_amount'];
				// if($Memcard&&$price>=$Memcard[0]['credits_discount']){
				// 			$Memcard = $Memcard[0];
				// 			while(($Memcard['card_balance']-=$Memcard['credits_use'])>=0&&($price-=$Memcard['credits_discount'])>=0){
				// 							$card_price+=$Memcard['credits_discount'];
				// 							$card_balance+=$Memcard['credits_use'];
				// 			}
				// 			$card_code = $Memcard['card_code'];
				// }
				// 折扣
				if ($Memcard&&$Memcard[0]['discount_set'] == 1) {
					if ($Memcard[0]['discount'] == $discount) {
						$discount = $discount*10;
						$order['discount'] = $discount;
					}
				}
				// 积分
				if ($Memcard&&$Memcard[0]['integral_dikou'] == 1) {
					$order['integral'] = $integral;
					$order['integral_money'] = $integral_money;
				} 
				// 余额
				if ($Memcard&&$Memcard[0]['balance_set'] == 1) {
					$order['user_money'] = $user_money;
				}

				// $id = db('merchants')->where('uid',$store_id)->field('id')->find();
				// $no_id = db('dc_no')->where(array('no'=>$no,'mid'=>$id['id']))->find();
				// if (!$no_id) {
				// 	return $this->error('参数错误');
				// }
				if($pack == 1){
					$mid = $this->_get_merchants($store_id);
					$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
					$goods_price += $dc_set['db_price'];
					$goods_price += $dc_set['ps_price'];
					$order['dc_db_price'] = $dc_set['db_price'];
					$order['dc_ps_price'] = $dc_set['ps_price'];
					$order['dc_db'] = 3;
				}else{
					$mid = $this->_get_merchants($store_id);
					$dc_set = db::name('merchants_dc_set')->where('mid',$mid)->find();
					$goods_price += $dc_set['ch_price']*$tableware;
					$order['dc_ch_price'] = $dc_set['ch_price']*$tableware;
				}
				// echo db('dc_no')->getLastSql();
				//开始插入数据
				//p($order_goods);
				// dump($order_goods_num);
				
				$order_benefit = $yh_price;
				
				$order['dc_ps_price'] = $ps_price;
				$order['order_goods_num'] = $order_goods_num;
				$order['user_note'] = $note;
				$order['total_amount'] = $goods_price + $ps_price;
				$order['order_amount'] = $order_amount;
				$order['order_benefit'] = $order_benefit;
				$order['order_sn'] = 'dc'.date('Ymdhis').rand(100000,999999);
				$order['mid'] = $uid;
				$order['user_id'] = $store_id;
				$order['order_status'] = 1;
				$order['paystyle'] = 1;
				$order['type'] = 2;
				$order['consignee'] = $address['name'];
				$order['mobile'] = $address['tel'];
				$order['address'] = $address['address'].$address['addresses'];
				$order['area_id']  =$address['area_id'];
				// dump($order);die;
				if(!$this->save($order)){
    					$this->rollback();
    					return $this->error('添加订单失败');
    			}
				$order_id = $this->getLastInsID();
				foreach($order_goods as &$v){
						$v['order_id'] = $order_id;
				}
				if(!db::name('order_goods')->insertAll($order_goods)){
    					$this->rollback();
    					return $this->error('添加商品失败');
    			}
    			$this->commit();
    			return $order_id;
		}
		/**
		 * 获取商家id
		 */
		public function _get_merchants($store_id)
		{
			$mid = db::name('merchants')->where('uid',$store_id)->field('id')->find();
			if (!$mid) {
				return $this->error('未找到商家');
			}
			return $mid['id'];
		}
		public function info($order_id=0,$member_id=0,$fields='*'){
    		$member_id || err('不存在id');
			$order_id || err('order_id is empty');
    		if(strpos($fields,',')||$fields=='*'){
    			$result = db('order')->where('member_id',$member_id)->where('order_id',$order_id)->field($fields)->find();
    		}else{
    			$result = db('order')->where('member_id',$member_id)->value($fields);
    		}
    		return $result;
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
		//判断库存
		public function error($msg){
				$this->error = $msg;
				return false;
		}

		//支付成功
		public function pay($out_trade_no,$transaction_id,$price,$bank_id=0,$paystyle = 1)
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
    				$order->pay_status = 1;
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
    				//清空购物车
    				if(!db::name('dc_cart')->where('store_id',$order->user_id)->where('uid',$order->mid)->delete()){
    					// file_put_contents('5.txt','购物车清空失败');
    					$this->rollback();
    					return $this->error('购物车清空失败');
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
    							db::name('pay')->insert($pay);
    				}
    				
    				$this->commit();
    				return $order->order_id;
		}

		//支付成功
		public function pay_new($out_trade_no,$transaction_id,$price,$bank_id=0,$paystyle = 1)
		{
			//查询订单是否支付
			if(!$order = $this->where(array('order_sn'=>$out_trade_no,'type'=>2))->field('order_id,order_amount,mid,user_id,coupon_code,order_status,mid,card_code,integral,mobile,add_time,user_money')->find()){
							err('没有找到订单');
			}
			if($order->order_status!==1){
					err('已经支付了');
			}
			//开启事务
			$this->startTrans();
			//修改订单状态
			$order->pay_time = time();
			$order->order_status = 5;
			$order->paystyle = $paystyle;
			$order->pay_status = 1;
			$order->transaction = $transaction_id;
			$order->real_price = $price;
			$order->save();
			//修改商品销量
			$order_goods = db::name('order_goods')->where('order_id',$order->order_id)->select();
			foreach ($order_goods as $key => $value) {
				$goods = db::name('goods')->where('goods_id',$value['goods_id'])->find();
				$goods['sales'] += $value['goods_num'];
				if(!db::name('goods')->where('goods_id',$value['goods_id'])->update(['sales'=> $goods['sales']])){
					$this->rollback();
					return $this->error('修改商品销量失败');
				}
				$good = db::name('goods')->where('goods_id',$value['goods_id'])->find();
			}
			//清空购物车
			if(!db::name('dc_cart')->where('store_id',$order->user_id)->where('uid',$order->mid)->delete()){
				$this->rollback();
				return $this->error('购物车清空失败');
			}
			$ScreenMemcardUse = model('ScreenMemcardUse');
    		$card = $ScreenMemcardUse->lists($order->user_id,$order->mid);
			//获得积分
			if($card){
				$card = $card[0];
				if ($card['credits_set']=='1') {
					$integral = (int)($price/$card['expense'])*$card['expense_credits'];
					add_log($integral);
					$integral&&$ScreenMemcardUse->cardOff($order->card_code,$integral,$out_trade_no);
				}
			}
			//扣除积分
			if($order->card_code){
				if($order->integral||$order->user_money){
					add_log(-$order->integral);
					add_log(-$order->user_money);
					$ScreenMemcardUse->cardOff($order->card_code,-$order->integral,$out_trade_no,$order->user_money);
				}
			}
			//核销优惠劵
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
				// $pay['cost_rate'] = db('merchants_pfpay')->where('merchant_id',$pay['merchant_id'])->value('wx_code');
				switch ($bank_id) {
					case 3:
						$pay['cost_rate'] = db('merchants_upwx')->where('mid',$pay['merchant_id'])->value('cost_rate');
						break;
					case 9:
						$pay['cost_rate'] = db('merchants_szlzwx')->where('mid',$pay['merchant_id'])->value('rate');
						break;
					case 10:
						$pay['cost_rate'] = db('merchants_pfpay')->where('merchant_id',$pay['merchant_id'])->value('wx_code');
						break;
					case 7:
						$pay['cost_rate'] = db('merchants_xypay')->where('merchant_id',$pay['merchant_id'])->value('wx_code');
						break;
					case 2:
						$pay['cost_rate'] = db('merchants_mpay')->where('expanderCd',$pay['merchant_id'])->value('weicodefen');
						break;
					default:
						$pay['cost_rate'] = 0;
						break;
				}
				db::name('pay')->insert($pay);
			}
			
			$this->commit();
			return $order->order_id;
		}
		
}