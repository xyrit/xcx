<?php
namespace app\index\controller;
use think\Db;
class Merchants extends Home
{
   	 	public function info(){
   	 			($store_id = input('store_id')) || err('store_id is empty');
				$info = model('merchants')->info($store_id);
			
				succ($info);
   	 	}
   	 	public function check_store(){
   	 			($store_id = input('store_id')) || err('store_id is empty');
   	 			($lon = input('lon')) || err('lon is empty');
   	 			($lat = input('lat')) || err('lat is empty');
   	 			($area_id = input('area_id')) || err('area_id is empty');
   	 			$data = model('merchants')->check_store($store_id,$lon,$lat,$area_id);
   	 			succ($data);
   	 	}

         /**
          * 检查商家设置
          */
         public function check_set(){
               ($store_id = input('store_id')) || err('store_id is empty');
               ($lon = input('lon')) || err('lon is empty');
               ($lat = input('lat')) || err('lat is empty');
               ($area_id = input('area_id')) || err('area_id is empty');
               $data = model('merchants')->check_set($store_id,$lon,$lat,$area_id);
               succ($data);
         }
}
