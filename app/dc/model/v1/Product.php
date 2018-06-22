<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Product extends Model
{
	public $name = 'goods';
	/**
	 * app 1.7 商品列表
	 */
	public function product_lists($store_id,$group_id=''){
		$where = array(
			'mid'=>$store_id,
			'put_xcx'=>2,
			'is_delete'=>0,
			'is_on_sale'=>1,
			'trade'=>2
			);
		if($group_id)$where['group_id']=$group_id;
		$data = $this->where($where)->field('goods_id,group_id,goods_name,shop_price,star,sales,goods_number,original_price,window_img')->order('mid')->select();
		$res = [];
		//抓取购物信息
		$cart = db('dc_cart')->field('id,goods_id,nums,attr_id')->where('store_id',$store_id)->where('uid',UID)->where('status',1)->select();
		$carts = [];
		global $cart_nums;
		$cart_nums = 0;
		global $cart_price;
		$cart_price = 0;
		$goods = model('product');
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
			$picture = $v['window_img'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$v['window_img'])){
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
			$data = db::name('goods_group')->field('group_id,group_name')->where(array('mid'=>$store_id,'trade'=>2))->order('sort')->select();
			return $data;
	}

	/***
	 * 商品详情
	 */
	public function info($goods_id){
		$info = $this->where('goods_id',$goods_id)->field('goods_id,goods_name,goods_number,is_sku,shop_price,goods_brief,star,original_price,window_img')->find();
		$picture = $info['window_img'];
		if(preg_match("/\x20*https?\:\/\/.*/i",$info['window_img'])){
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
	}

	/**
	 * 商品简单信息
	 */
	public function lite_info($goods_id,$attr_id){
		$info = $this->where('goods_id',$goods_id)->field('goods_id,mid,goods_name,goods_number,is_sku,shop_price,window_img')->find();
		$picture = $info['window_img'];
		if(preg_match("/\x20*https?\:\/\/.*/i",$info['window_img'])){
		    $info->goods_img1 = $picture;
		}else{
		    $info->goods_img1 = URL.$picture;
		}
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
	public function check_good($goods_id,$attr_id,$nums)
	{
		$info = $this->where('goods_id',$goods_id)->lock(true)->field('goods_id,mid,goods_name,goods_number,is_sku,shop_price,window_img,put_xcx')->find();
		if($info){
			$picture = $info['window_img'];
			if(preg_match("/\x20*https?\:\/\/.*/i",$info['window_img'])){
			    $info->picture = $picture;
			}else{
			    $info->picture = URL.$picture;
			}
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
		}else{
			return false;
		}
	}

	public function error($msg){
		$this->error = $msg;
		return false;
	}

}