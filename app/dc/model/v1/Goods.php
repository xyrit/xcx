<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Goods extends Model
{
	/**
	 * 商品列表
	 */
	public function lists($store_id){
			$data = $this->where(array('mid'=>$store_id,'put_xcx'=>2,'is_delete'=>0,'is_on_sale'=>1))->field('goods_id,group_id,goods_name,shop_price,goods_img1,star,sales,goods_number,original_price')->order('mid')->select();
			$res = [];
			//抓取购物信息
			$cart = db('dc_cart')->field('id,goods_id,nums,attr_id')->where('store_id',$store_id)->where('uid',UID)->where('status',1)->select();
			$carts = [];
			global $cart_nums;
			$cart_nums = 0;
			global $cart_price;
			$cart_price = 0;
			$goods = model('goods');
			foreach($cart as $v){
						//查询是否下架
						$goods_info =$goods->check_good($v['goods_id'],$v['attr_id'],$v['nums']);
						//查询订单价格
						if($goods_info==false){
								$v['status']=0;
								db('dc_cart')->where('id',$v['id'])->value('status',0);
								continue;
						}
						$cart_price += $goods_info->shop_price*$v['nums'];
						$cart_nums+=$v['nums'];
						isset($carts[$v['goods_id']])?$carts[$v['goods_id']] += $v['nums']:$carts[$v['goods_id']] = $v['nums'];
			}
			foreach($data as $v){
					if(isset($carts[$v->goods_id])){
							$v->nums = $carts[$v->goods_id];
					}
					// $v->goods_img1 = URL.$v->goods_img1;
					$picture = $v['goods_img1'];
					if(preg_match("/\x20*https?\:\/\/.*/i",$v['goods_img1'])){
					    $v->goods_img1 = $picture;
					}else{
					    $v->goods_img1 = URL.$picture;
					}
					$res[$v->group_id][] = $v->data;
			}
			$result = [];
			foreach($res as $v){
					$result[] = $v;	
			}
			return $result;
	}
	/**
	 * 商品分类
	 */
	public function group($store_id){
			$data = db::name('goods_group')->field('group_id,group_name')->where('mid',$store_id)->order('sort')->select();
			return $data;
	}
	/***
	 * 商品详情
	 */
	public function info($goods_id){
			$info = $this->where('goods_id',$goods_id)->field('goods_id,goods_name,goods_img1,goods_number,is_sku,shop_price,goods_brief,star,original_price')->find();
			// $info->goods_img1 = URL.'/'.$info->goods_img1;
			$picture = $info['goods_img1'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$info['goods_img1'])){
			    $info->goods_img1 = $picture;
			}else{
			    $info->goods_img1 = URL.$picture;
			}
			$cart = db('dc_cart')->field('nums,attr_id')->where('uid',UID)->where('goods_id',$goods_id)->select();
			if($info['is_sku']){
					$carts = [];
					foreach($cart as $v){
							$carts[$v['attr_id']] = $v['nums'];
					}
					$sku = db::name('goods_sku')->where('goods_id',$goods_id)->select();
					foreach($sku as &$v){
							if(isset($carts[$v['sku_id']])){
									$v['nums'] = $carts[$v['sku_id']];
							}
					}
					$info->sku = $sku;		
			}
			return $info;
			//p($info);
			
	}
	/**
	 * 商品简单信息
	 */
	public function lite_info($goods_id,$attr_id){
			$info = $this->where('goods_id',$goods_id)->field('goods_id,mid,goods_name,goods_img1 as picture,goods_number,is_sku,shop_price,put_xcx')->find();
			if($attr_id){
					$data = db::name('goods_sku')->where('sku_id',$attr_id)->where('goods_id',$goods_id)->field('quantity,price,properties')->find();
					$info->properties = $data['properties'];
					$info->goods_number = $data['quantity'];
					$info->shop_price = $data['price'];
			}
			return $info;
	}
	/**
	 * 检查库存
	 */
	public function check_good($goods_id,$attr_id,$nums){
			
			$info = $this->where('goods_id',$goods_id)->lock(true)->field('goods_id,mid,goods_name,goods_img1 as picture,goods_number,is_sku,shop_price,put_xcx')->find();
			if(!($info->is_sku==(bool)$attr_id)){
					return false;
			}
			if($info->goods_number<$nums){
							return false;
			}
			if($info->put_xcx!=2){
				return false;
			}
			if($attr_id){
					$data = db::name('goods_sku')->where('sku_id',$attr_id)->lock(true)->where('goods_id',$goods_id)->field('quantity,price,properties')->find();
					$info->properties = $data['properties'];
					$info->goods_number = $data['quantity'];
					$info->shop_price = $data['price'];
			}
			return $info;
	}
	public function error($msg){
			$this->error = $msg;
			return false;
	}


}