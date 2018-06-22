<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Store as StoreModel;
use think\Controller;
use think\Db;
class Store extends Home
{
//	protected function _initialize(){
//		 parent::_initialize();
//	  	 p('store_initialize'); 	
//	}
    public function index()
    {
    	 //检测store_id is empty
    	  ($store_id = input('store_id')) || err('store_id is empty');
           //店铺信息
    	  $StoreModel = new StoreModel;
    	  $info = $StoreModel->info($store_id);
    	  succ($info);
    }
    public function distance_store(){
		$lon = input('lon',0);
		$lat = input('lat',0);
		$data = model('Merchants')->distance_store($lon,$lat,1000,input('area'),input('admin_id',0));
		succ($data);
	}
}
