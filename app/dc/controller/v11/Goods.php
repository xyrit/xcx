<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Goods as GoodsModel;
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
    		$group = $Goods->group($store_id);
    		$data['group'] = $group;
    		succ($data);
    		
    }
}
