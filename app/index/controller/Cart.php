<?php
namespace app\index\controller;
use think\Db;

class Cart extends Home
{
	public function lists(){
				($store_id = input('store_id')) || err('store_id is empty');
				$cart = Model('cart')->lists($store_id);
				succ($cart);
	}
	//http://127.0.0.1/BLT/wap/cart/update?token=75e1e3f2378bcbdb89cd6389dc5e8410&&attr=76-71
	public function add($goods_id=0,$attr_id=0,$nums=1){	
			$cart = model('cart');
			if($cart->add($goods_id,$attr_id,$nums,UID)){
					succ();
			}else{
					err($cart->getError());
			}
	}
	public function update($id=0,$nums=0){
				($id = input('id',0)) || err('id is empty');
				($nums = input('nums',0)) || err('nums is empty');
				 $cart = model('cart');
				 $cart->to_update($id,$nums,UID)?succ():err($cart->getError());
	}
	public function delete(){
				($id  = input('id')) || err('id is empty');
				$cart = model('cart');
				if($cart->del($id,UID)){
						succ();
				}else{
						err();
				}
				
	}

	/**
	 *	app 1.7版本 购物车列表 
	 */
	public function cart_lists(){
		($store_id = input('store_id')) || err('商家用户id为空');
		$cart = Model('cart')->cart_lists($store_id);
		succ($cart);
	}

	/**
	 *  app1.7版本 添加到购物车
	 */
	public function cart_add()
	{	
		($goods_id = input('goods_id'))||err('商品ID不能为空');
		$attr_id = input('attr_id',0);
		$nums = input('nums',1);
		$cart = model('cart');
		if($cart->cart_add($goods_id,$attr_id,$nums,UID)){
			succ('添加成功');
		}else{
			err($cart->getError());
		}
	}

	public function cart_update(){
		($id = input('id',0)) || err('id is empty');
		($nums = input('nums',0)) || err('nums is empty');
		$cart = model('cart');
		$cart->cart_update($id,$nums,UID)?succ('更新成功'):err($cart->getError());
	}
}
