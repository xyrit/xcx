<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Product extends controller
{
	/**
	 * 商品列表
	 * @return [type] [description]
	 */
    public function lists()
    {
    		($store_id = input('store_id')) || err('store_id is mepty');
    		$lists = model('product')->lists($store_id,input('group_id'));
    		
			succ($lists);
    }

    /**
     * 首页热门商品
     * @return [type] [description]
     */
    public function index(){
        
        ($store_id = input('store_id',0)) || err('store_id is empty');
      $banner = model('Banner')->lists($store_id);
        //获取所有商品
        $lists = model('product')->sell_hot($store_id);
        $data['banner'] = $banner;
        $data['lists'] = $lists;
        //获取店铺信息
        $data['store_info'] = model('merchants')->info($store_id);
        succ($data);
    } 

    //加载更多热门商品
    public function hot()
    {
            ($store_id = input('store_id')) || err('store_id is mepty');
            $lists = model('product')->sell_hot($store_id);
            
            succ($lists);
    }
    public function info(){
    		($goods_id = input('id')) || err('id is empty');
    		$data = model('product')->info($goods_id);
    		$data['user_phone'] = db('merchants_users')->where('id',$data['mid'])->value('user_phone');
    		$data['pj'] = model('pj')->news($goods_id);
    		succ($data);
    }
    public function pj(){
    		($goods_id = input('goods_id')) || err('goods_id is empty');
    		$lists = model('pj')->lists($goods_id);
    		succ($lists);
    }
    /*
     * 获取商品分类
     */
    public function category(){
    		($store_id  = input('store_id')) || err('store_id is empty');
    		$category = model('group');
    		$data = $category->group_lists($store_id);
    		succ($data);
    }
    public function class_lists(){
    			Db::name('goods_class')->where('status',1) -> select();
    }

    /**
     * 搜索商品
     */
    public function search_goods()
    {
        ($search_name = input('search_name')) || err('关键词为空'); //搜索关键词
        ($store_id  = input('store_id')) || err('store_id is empty');   //商户uid
        $goods = model('product');
        $data = $goods->search_lists($search_name,$store_id);
        if ($data) {
            succ($data);
        }else{
            succ('商品为空');
        }
    }

    /**
     * 新品上市列表
     */
    public function news()
    {
        ($store_id = input('store_id')) || err('store_id is mepty');
        $lists = model('product')->news($store_id);
        succ($lists);
    }

    /**
     * 分类商品
     */
    public function classify(){
        ($store_id = input('store_id',0)) || err('store_id is empty');
        $group = model('group')->group_lists($store_id);
        $group_id = empty($group)?0:$group[0]['group_id'];
        //获取所有商品
        $lists = model('product')->classify($store_id,$group_id);
        $data['cat_lists'] = $group;
        $data['lists'] = $lists;
        $data['group_id'] = $group_id;
        succ($data);
    }

    /**
     * 分类商品 新
     */
    public function classGoods(){
        ($store_id = input('store_id',0)) || err('store_id is empty');
        $group_id = input('group_id',0);
        if ($group_id) {
        	$data = model('product')->lists($store_id,input('group_id'));
        }else{
        	$group = model('group')->groupLists($store_id);
	        $group_id = empty($group)?0:$group[0]['group_id'];
	        //获取所有商品
	        $lists = model('product')->classGoods($store_id,$group_id);
	        $data['cat_lists'] = $group;
	        $data['lists'] = $lists;
	        $data['group_id'] = $group_id;
        }
        succ($data);
    }
}