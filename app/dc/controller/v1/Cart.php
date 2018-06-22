<?php
namespace app\dc\controller\v1;
use think\Controller;
use think\Db;
class Cart extends Home
{
	public function lists(){
			($store_id = input('store_id')) || err('store_id is empty');
			$Cart = model('cart');
			$data = $Cart->lists($store_id,UID);
			succ($data);
			
	}
		public function add(){	
			($goods_id = input('goods_id')) || err('goods_id is empty');
			($nums = input('nums')) || err('nums is empty');
			$attr_id = input('attr_id');
			$Cart = model('cart');
			if($Cart->add($goods_id,$attr_id,$nums,UID)){
					succ();
			}else{
					err($Cart->getError());
			}
	}
	public function update(){
			($id = input('id')) || err('id is empty');
			($nums = input('nums')) || err('nums is empty');
			$Cart = model('cart');
			if($Cart->to_update($id,$nums,UID)){
					succ();
			}else{
					err($Cart->getError());
			}
	}
	public function delete(){
				$id  = input('id');
				($store_id = input('store_id')) || err('store_id is empty');
				$cart = model('cart');
				if($cart->del($store_id,UID,$id)){
						succ();
				}else{
						err('删除失败');
				}
	}

	/**
	 * app1.7 购物车列表
	 * @return store_id 商家用户id
	 */
	public function cart_lists(){
		($store_id = input('store_id')) || err('store_id is empty');
		$Cart = model('cart');
		$data = $Cart->cart_lists($store_id,UID);
		succ($data);
	}

	/**
	 * app1.7 添加到购物车
	 * @return attr_id 规格id
	 */
	public function cart_add(){	
		($goods_id = input('goods_id')) || err('goods_id is empty');
		($nums = input('nums')) || err('nums is empty');
		$attr_id = input('attr_id');
		$Cart = model('cart');
		if($Cart->cart_add($goods_id,$attr_id,$nums,UID)){
			succ('添加成功');
		}else{
			err($Cart->getError());
		}
	}

	
}
