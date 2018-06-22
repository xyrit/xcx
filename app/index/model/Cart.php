<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Cart extends Model
{
		protected $createTime =  '';
		protected $updateTime = '';
		public function lists($store_id){
				$data = $this->where('uid',UID)->where('store_id',$store_id)->select();
				$goods = model('goods');
				$carts = [];
				foreach($data as $v){
						if($v->attr_id){
								$goods_info = 	Db::name('goods_sku')
												->alias('a')
												->join('goods b','a.goods_id = b.goods_id')
												->where('sku_id',$v->attr_id)->field('quantity as goods_number,a.price,properties,is_on_xcx,is_delete')->find();
								
								
						}else{
								$goods_info = Db::name('goods')->where('goods_id',$v->goods_id)->field('shop_price as price,goods_number,is_on_xcx,is_delete')->find();
						}
						$info = json_decode($v->goods_info,true);
						$cart['id'] = $v->id;
						$cart['goods_id'] = $v->goods_id;
						$cart['attr_id'] = $v->attr_id;
						$cart['properties'] = isset($goods_info['properties'])?$goods_info['properties']:'';
						$cart['attr_id'] = $v->attr_id;
						$cart['goods_name'] = $info['goods_name'];
						$picture = $info['goods_img1'];
						if(preg_match("/\x20*https?\:\/\/.*/i",$info['goods_img1'])){
						    $cart['picture'] = $picture;
						}else{
						    $cart['picture'] = URL.$picture;
						}
						$cart['price'] = $goods_info['price'];
						
						if($goods_info['goods_number']<=0 || $goods_info['is_on_xcx'] == 2 || $goods_info['is_delete'] == 1 || $goods_info['put_xcx'] != 2){
								$cart['status'] = 0 ;
						}else{
								$cart['status'] = 1;
						}
						if($v->nums > $goods_info['goods_number']){
								$v->nums = 1;
								$v->save();
						}
						$cart['nums'] = $v->nums;	
						$carts[] = $cart;	
				}
				return $carts;
		}
		public function add($goods_id,$attr_id=0,$nums=1,$uid){
			   	//查询商品的信息
				$data = $this->where('goods_id',$goods_id)->where('attr_id',$attr_id)->where('uid',$uid)->find();
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
					  		return $this->error("库存不足");
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
		public function to_update($id=0,$nums=0,$uid){
					$cart = $this->where('id',$id)->where('uid',$uid)->find();
					
					if(!$cart){
							return $this->error('不存在该购物车');
					}
					
					if($cart->attr_id){
						//判断是否存在库存是否足够
						if(db::name('goods_sku')->where('sku_id',$cart->attr_id)->where('goods_id',$cart['goods_id'])->value('quantity')<$nums){
									return $this->error('库存不足');
						}
					}else{
						if(db::name('goods')->where('goods_id',$cart['goods_id'])->value('goods_number')<$nums){
								return $this->error('库存不足');
						}
					}
					$cart->nums = $nums;
					return $cart->save()!==false?true:$this->error('失败');
		}
		public function del($id,$uid){
				return $this->where('id',$id)->where('uid',$uid)->delete();
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

		/**
		 * 购物车列表
		 * @param  [string] $store_id [商家用户id]
		 * @return [type]           [description]
		 */
		public function cart_lists($store_id)
		{
			$data = $this->where('uid',UID)->where('store_id',$store_id)->select();
			$goods = model('goods');
			$carts = [];
			foreach($data as $v){
				if($v->attr_id){
					$goods_info = Db::name('goods_sku')
					->alias('a')
					->join('goods b','a.goods_id = b.goods_id')
					->where('sku_id',$v->attr_id)->field('quantity as goods_number,a.price,properties,put_xcx,is_delete')->find();
				}else{
					$goods_info = Db::name('goods')->where('goods_id',$v->goods_id)->field('shop_price as price,goods_number,put_xcx,is_delete')->find();
				}
				$info = json_decode($v->goods_info,true);
				$cart['id'] = $v->id;
				$cart['goods_id'] = $v->goods_id;
				$cart['attr_id'] = $v->attr_id;
				$cart['properties'] = isset($goods_info['properties'])?$goods_info['properties']:'';
				$cart['attr_id'] = $v->attr_id;
				$cart['goods_name'] = $info['goods_name'];
				$picture = $info['goods_img1'];
				if(preg_match("/\x20*https?\:\/\/.*/i",$info['goods_img1'])){
				    $cart['picture'] = $picture;
				}else{
				    $cart['picture'] = URL.$picture;
				}
				$cart['price'] = $goods_info['price'];
				
				if($goods_info['goods_number']<=0 || $goods_info['put_xcx'] == 0 || $goods_info['is_delete'] == 1){
						$cart['status'] = 0 ;
				}else{
						$cart['status'] = 1;
				}
				if($v->nums > $goods_info['goods_number']){
						$v->nums = 1;
						$v->save();
				}
				$cart['nums'] = $v->nums;	
				$carts[] = $cart;	
			}
			return $carts;
		}

		/**
		 * 添加购物车
		 * @param [type]  $goods_id 商品id
		 * @param integer $attr_id  规格id
		 * @param integer $nums     商品数量
		 * @param [type]  $uid      会员id
		 */
		public function cart_add($goods_id,$attr_id=0,$nums=1,$uid){
		   	//查询商品的信息
			$data = $this->where('goods_id',$goods_id)->where('attr_id',$attr_id)->where('uid',$uid)->find();
			//更新
			if($data){
				$data->nums += $nums;
				if($data->nums<=0){
			  		return $this->error('商品数量最小为1');
			    }
			    $goods = model('product');
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
				if(!$goods_info = model('product')->check_good($goods_id,$attr_id,$nums)){
					return $this->error("库存不足");
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

		/**
		 * 更新购物车
		 * @param  integer $id   购物车id
		 * @param  integer $nums 商品数量
		 * @param  [type]  $uid  会员id
		 * @return [type]        [description]
		 */
		public function cart_update($id=0,$nums=0,$uid){
			$cart = $this->where('id',$id)->where('uid',$uid)->find();
			if(!$cart){
				return $this->error('不存在该购物车');
			}
			if($cart->attr_id){
				//判断是否存在库存是否足够
				if(db::name('goods_sku')->where('sku_id',$cart->attr_id)->where('goods_id',$cart['goods_id'])->value('quantity')<$nums){
					return $this->error('库存不足');
				}
			}else{
				if(db::name('goods')->where('goods_id',$cart['goods_id'])->value('goods_number')<$nums){
					return $this->error('库存不足');
				}
			}
			$cart->nums = $nums;
			return $cart->save()!==false?true:$this->error('失败');
		}

}