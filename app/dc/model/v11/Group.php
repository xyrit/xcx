<?php
namespace app\index\model\v1;
use think\Model;
use think\Db;
class Group extends Model
{	
		public $name = 'goods_group';
		public function lists($store_id){
			return  $this->where('mid',$store_id)->field('group_id,group_name')->order('sort')->select();
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}