<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Goods as GoodsModel;
use app\dc\model\v1\Product;
use think\Controller;
use think\Db;
class Goods extends Home
{
    public function index(GoodsModel $Goods)
    {	
    	($store_id = input('store_id')) || err('store_id is empty');
		//商品列表
		$data['lists'] = $Goods->lists($store_id);
		//商品分组
        
		$group = model('group')->lists($store_id);
		$data['group'] = $group;
        //优惠劵状态
        $coupon = model('coupons');
        $coupons = $coupon->lists($store_id,UID);
        foreach ($coupons as $key => $value) {
            $value['end_timestamp'] = date('Y-m-d',$value['end_timestamp']);
        }
        //商家设置
        $dc = model('Dcset');
        $dc_set = $dc->lists($store_id);
        $data['qs_price'] = $dc_set['qs_price'];
        $data['ps_price'] = $dc_set['ps_price'];
        $data['coupons'] = $coupons;
		$data['cart_nums'] = $GLOBALS['cart_nums'];
		$data['cart_price'] = $GLOBALS['cart_price'];
		succ($data);
    }
    /**
     * 商品信息
     */
    public function info(GoodsModel $Goods){
		($goods_id = input('goods_id')) || err('goods_id is empty');
		$info = $Goods->info($goods_id);
		succ($info);
    		
    }

    /**
     * app 1.7 商品列表
     */
    public function product_lists(Product $Product)
    {     
        ($store_id = input('store_id')) || err('store_id is empty');
        //商品列表
        $data['lists'] = $Product->product_lists($store_id);
        //商品分组
        // $group = $Product->group($store_id);
        $group = model('group')->lists($store_id);
        // $group_id = empty($group)?0:$group[0]['group_id'];
        $data['group'] = $group;
        // $data['group_id'] = $group_id;
        //优惠劵状态
        $coupon = model('coupons');
        $coupons = $coupon->lists($store_id,UID);
        foreach ($coupons as $key => $value) {
            $value['end_timestamp'] = date('Y-m-d',$value['end_timestamp']);
        }
        //商家设置
        $dc = model('Dcset');
        $dc_set = $dc->lists($store_id);
        $data['qs_price'] = $dc_set['qs_price'];
        $data['ps_price'] = $dc_set['ps_price'];
        $data['coupons'] = $coupons;
        $data['cart_nums'] = $GLOBALS['cart_nums'];
        $data['cart_price'] = $GLOBALS['cart_price'];
        succ($data);
    }

    /**
     * app 1.7 商品详情
     */
    public function product_info(Product $Product){
        ($goods_id = input('goods_id')) || err('goods_id is empty');
        $info = $Product->info($goods_id);
        succ($info);
            
    }
}
