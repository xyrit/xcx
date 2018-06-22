<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Group extends Model
{	
	public $name = 'goods_group';
	public function lists($store_id){
		$where['mid'] = $store_id;
		$where['gid'] = '0';
		$data = $this->where($where)->field('group_id,group_name,gid,sort')->order('sort')->select();
		//查看是否有商品
		$lists = model('Goods')->lists($store_id);
		foreach ($data as $key => $value) {
			$map['gid'] = $value['group_id'];
			$map['mid'] = $store_id;
			$res = $this->where($map)->field('group_id,group_name,gid,sort')->order('sort')->select();
			$value['res'] = $res;
		}	
		if(json_decode(json_encode($lists),true)['total']){
				$data[] = ['group_id'=>0,'group_name'=>'其他'];
		}
		return $data; 
	}

	/**
	 * app1.7 分组列表
	 */
	public function group_lists($store_id){
		$where['mid'] = $store_id;
		$where['gid'] = '0';
		$where['trade'] = 1;
		$data = $this->where($where)->field('group_id,group_name,gid,sort')->order('sort')->select();
		//查看是否有商品
		$lists = model('product')->lists($store_id);
		foreach ($data as $key => $value) {
			$map['gid'] = $value['group_id'];
			$map['mid'] = $store_id;
			$map['trade'] = 1;
			$res = $this->where($map)->field('group_id,group_name,gid,sort')->order('sort')->select();
			if (!$res) {
				if(!db::name('goods')->where(array('group_id'=>$value['group_id'],'is_delete'=>0,'put_xcx'=>2,'trade'=>1))->find()){
					unset($data[$key]);
				}
			}else{
				$unse = array();
				foreach ($res as $k => $v) {
					if(!db::name('goods')->where(array('group_id'=>$v['group_id'],'is_delete'=>0,'put_xcx'=>2,'trade'=>1))->find()){
						unset($res[$k]);
					}
				}
				if (!$res) {
					unset($data[$key]);
				}
			}
			$res = array_values($res);
			$value['res'] = $res;
		}	
		if(json_decode(json_encode($lists),true)['total']){
				$data[] = ['group_id'=>0,'group_name'=>'其他'];
		}
		$data = array_values($data);
		return $data; 
	}

	public function groupLists($store_id){
		$where['mid'] = $store_id;
		$where['gid'] = '0';
		$where['trade'] = 1;
		$data = $this->where($where)->field('group_id,group_name,gid,sort')->order('sort')->select();
		//查看是否有商品
		$lists = model('product')->lists($store_id);
		$un = array();
		foreach ($data as $key => $value) {
			$map['gid'] = $value['group_id'];
			$map['mid'] = $store_id;
			$map['trade'] = 1;
			$res = $this->where($map)->field('group_id,group_name,gid,sort')->order('sort')->select();
			
			if (!$res) {
				if(!db::name('goods')->where(array('group_id'=>$value['group_id'],'is_delete'=>0,'put_xcx'=>2,'trade'=>1))->find()){
					unset($data[$key]);
				}
			}else{
				$unse = array();
				foreach ($res as $k => $v) {
					if(!db::name('goods')->where(array('group_id'=>$v['group_id'],'is_delete'=>0,'put_xcx'=>2,'trade'=>1))->find()){
						unset($res[$k]);
					}
				}
				if (!$res) {
					unset($data[$key]);
				}
			}
			$res = array_values($res);
			$value['res'] = $res;
			// dump($un);
		}
		// dump($un);
		if(json_decode(json_encode($lists),true)['total']){
				$data[] = ['group_id'=>0,'group_name'=>'其他'];
		}
		$data = array_values($data);
		return $data; 
	}

	public function error($msg){
			$this->error = $msg;
			return false;
	}

}