<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Group extends Model
{	
		public $name = 'goods_group';
		public function lists($store_id){
			$where['mid'] = $store_id;
			$where['trade'] = 2;
			$data = $this->where($where)->field('group_id,group_name,gid,sort')->order('sort')->select();
			//查看是否有商品
			$groups = array();
			foreach ($data as $key => $value) {
				// $map['gid'] = $value['group_id'];
				// $map['mid'] = $store_id;
				// $res = $this->where($map)->field('group_id,group_name,gid,sort')->order('sort')->select();
				// $value['res'] = $res;
				if(model('product')->product_lists($store_id,$value['group_id'])){
					array_push($groups, $value);
				}else{
					
				}
			}	
			if(db::name('goods')->where(array('mid'=>$store_id,'group_id'=>0))->select()){
					$groups[] = ['group_id'=>0,'group_name'=>'其他','gid'=>0];
			}
			return $groups; 
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}