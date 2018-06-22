<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Category extends Model
{
		public function lists($store_id,$pid=0){
			return $this->where('store_id',$store_id)->where('parent_id',$pid)->field('cat_id,cat_name')->select();
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}