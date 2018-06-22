<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Cart extends Model
{
	public $name = 'dc_cart';
	protected $createTime =  '';
	protected $updateTime = '';
	public function index($store_id,$uid){
			$data = $this->where('uid',$uid)->where('store_id',$store_id)->select();
			p($data);
	}
	public function lists1($uid){
			$data = $this->where('uid',$uid)->select();
			$goods = model('goods');
			$carts = [];
			$result =  $res= [];
			foreach($data as $key=>$v){
					isset($res[$v['store_id']]) || ($res[$v['store_id']] = ['store_name'=>$v->store_name,'store_id'=>$v->store_id]);
					if($v->attr_id){
							$goods_info = Db::name('goods')
											->alias('a')
											->join('goods_sku b ',' a.id = b.goods_id','LEFT')
											->where('a.id',$v->goods_id)->where('b.attr',$v->attr_id)->field('b.price,b.sku')->find();
					}else{
						   $goods_info = Db::name('goods')->where('id',$v->goods_id)->find();
					}
					if(empty($goods_info) || $goods_info['sku']<1){
							$goods_info['status'] = 0;
					}else{
							$goods_info['status'] = 1;
							if($goods_info['sku'] < $v->nums){
								$v->nums = 1;
								$data->save();
							}
					}
					$goods_info['goods_id'] = $v->goods_id;
					$goods_info['attr_id'] = $v->attr_id;
					$goods_info['attr'] = get_attr_name($v->attr_id);
					$goods_info['picture'] = URL.$v->picture;
					$goods_info['goods_name'] = $v->goods_name;
					$goods_info['num'] = $v->nums;
					$goods_info['id'] = $v->id;
					$res[$v['store_id']]['_lists'][] = $goods_info;
			}
			sort($res);
			return $res;
	}
	
	public function lists($store_id,$uid,$is_check=false){
		
			$cart = $this->where('store_id',$store_id)->where('uid',$uid)->select();

			$goods = model('goods');
			global $cart_nums;
			global $cart_price;
			$cart_price = $cart_nums = 0;
			foreach($cart as $key=>$v){
						if($is_check==false){
								$goods_info = $goods->lite_info($v['goods_id'],$v['attr_id']);
						}else{
						//查询是否下架
								$goods_info =$goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
								//查询订单价格
								if($goods_info==false){
										$v->status=0;
										$cart->save();
										continue;
								}
						}
						isset($goods_info['properties']) && ($cart[$key]->properties = $goods_info->properties);
						$cart[$key]->goods_price = $goods_info->shop_price;
						$cart_price += $goods_info->shop_price*$v['nums'];
						$cart_nums+=$v['nums'];
			}

			return $cart;
//				$goods = model('goods');
//				$carts = [];
//				$result =  $res= [];
//				foreach($data as $key=>$v){
//						isset($res[$v['store_id']]) || ($res[$v['store_id']] = ['store_name'=>$v->store_name,'store_id'=>$v->store_id]);
//						if($v->attr_id){
//								$goods_info = Db::name('goods')
//												->alias('a')
//												->join('goods_sku b ',' a.id = b.goods_id','LEFT')
//												->where('a.id',$v->goods_id)->where('b.attr',$v->attr_id)->field('b.price,b.sku')->find();
//						}else{
//							   $goods_info = Db::name('goods')->where('id',$v->goods_id)->find();
//						}
//						if(empty($goods_info) || $goods_info['sku']<1){
//								$goods_info['status'] = 0;
//						}else{
//								$goods_info['status'] = 1;
//								if($goods_info['sku'] < $v->nums){
//									$v->nums = 1;
//									$data->save();
//								}
//						}
//						$goods_info['goods_id'] = $v->goods_id;
//						$goods_info['attr_id'] = $v->attr_id;
//						$goods_info['attr'] = get_attr_name($v->attr_id);
//						$goods_info['picture'] = URL.$v->picture;
//						$goods_info['goods_name'] = $v->goods_name;
//						$goods_info['num'] = $v->nums;
//						$goods_info['id'] = $v->id;
//						$res[$v['store_id']]['_lists'][] = $goods_info;
//				}
//				sort($res);
		
	}
	
	public function add($goods_id,$attr_id=0,$nums=1,$uid){
		   	//查询商品的信息
			$data = $this->where('goods_id',$goods_id)->where('attr_id',$attr_id)->where('uid',$uid)->find();
			
			//更新
			if($data){
					$data->nums += $nums;
					
					if($data->nums<=0){
						$this->where('id',$data->id)->delete();
				  		return true;
				    }
				    $goods = model('goods');
					if(!$goods_info = $goods->check_good($goods_id,$attr_id,$data->nums)){
						
				  		return $this->error('库存不足');
				 	}
				 
				 	if($data->save()){
				  			return true;
					}else{
					  	return $this->error('添加失败');
					}
			//添加
			}else{
				  if($nums<=0){
				  				return  $this->error('商品数量大于1');
				  }
				  if(!$goods_info = model('goods')->check_good($goods_id,$attr_id,$nums)){
				  				return  $this->error("库存不足");
				  }
				  //查看库存
				  $this->uid = UID;
				  $this->goods_id = $goods_id;
				  $this->store_id = $goods_info->mid;
				  //$this->store_name = get_store($goods_info->mid,'name');
				  $this->goods_name = $goods_info->goods_name;
				  $this->goods_picture= $goods_info->picture;
				  $this->attr_id = $attr_id;
				  $this->nums = $nums;
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
				$cart->nums += $nums;
				if($cart->nums<=0){
						$this->where('id',$cart->id)->delete();
				  		return true;
				}
				$goods = model('goods');
				if(!$goods_info = $goods->check_good($cart->goods_id,$cart->attr_id,$cart->nums)){
				  		return $this->error($goods->getError());
				}
//					$cart->nums = $nums;
				
				return $cart->save()!==false?true:$this->error('失败');
	}
	public function del($store_id,$uid,$id){
			if($id){
				
			}else{
				return $this->where('store_id',$store_id)->where('uid',$uid)->delete();
			}
			
	}
	public function error($msg){
			$this->error = $msg;
			return false;
	}

	/**
	 * app 1.7购物车列表
	 * @param  [type]  $store_id 商家用户id
	 * @param  [type]  $uid      会员id
	 * @param  boolean $is_check [description]
	 * @return [type]            [description]
	 */
	public function cart_lists($store_id,$uid,$is_check=false)
	{
		$cart = $this->where('store_id',$store_id)->where('uid',$uid)->select();
		$goods = model('product');
		global $cart_nums;
		global $cart_price;
		$cart_price = $cart_nums = 0;
		foreach($cart as $key=>$v){
			if($is_check==false){
				$goods_info = $goods->lite_info($v['goods_id'],$v['attr_id']);
			}else{
				//查询是否下架
				$goods_info =$goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
				//查询订单价格
				if($goods_info==false){
					$v->status=0;
					$cart->save();
					continue;
				}
			}
			isset($goods_info['properties']) && ($cart[$key]->properties = $goods_info->properties);
			$cart[$key]->goods_price = $goods_info->shop_price;
			$cart_price += $goods_info->shop_price*$v['nums'];
			$cart_nums+=$v['nums'];
		}
		return $cart;
	}
	
	public function cart_add($goods_id,$attr_id=0,$nums=1,$uid){
	   	//查询商品的信息
		$data = $this->where('goods_id',$goods_id)->where('attr_id',$attr_id)->where('uid',$uid)->find();
		
		//更新
		if($data){
			$data->nums += $nums;
			if($data->nums<=0){
				$this->where('id',$data->id)->delete();
		  		return true;
		    }
		    $goods = model('product');
			if(!$goods_info = $goods->check_good($goods_id,$attr_id,$data->nums)){
				return $this->error('库存不足');
		 	}
		 
		 	if($data->save()){
		  		return true;
			}else{
			  	return $this->error('添加失败');
			}
		//添加
		}else{
		  if($nums<=0){
		  				return  $this->error('商品数量大于1');
		  }
		  if(!$goods_info = model('product')->check_good($goods_id,$attr_id,$nums)){
		  				return  $this->error("库存不足");
		  }
		  //查看库存
		  $this->uid = UID;
		  $this->goods_id = $goods_id;
		  $this->store_id = $goods_info->mid;
		  //$this->store_name = get_store($goods_info->mid,'name');
		  $this->goods_name = $goods_info->goods_name;
		  $this->goods_picture= $goods_info->picture;
		  $this->attr_id = $attr_id;
		  $this->nums = $nums;
		  if($this->save()){
		  	return true;
		  }else{
		  	return $this->error('添加失败');
		  }
		}
	}
	
}