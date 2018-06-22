<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Goods extends Model
{
		/**
		 * 商品列表
		 */
		public function lists($store_id){
				$data = $this->where('mid',$store_id)->field('goods_id,group_id,goods_name')->order('mid')->select();
				
				$res = [];
				foreach($data as $v){
						$res[$v->group_id][] = $v->data;
				}
				$result = [];
				foreach($res as $v){
						$result[] = $v;	
				}
				return $result;
		}
		/**
		 * 商品分类
		 */
		public function group($store_id){
				$data = db::name('goods_group')->field('group_id,group_name')->where('mid',$store_id)->order('sort')->select();
				return $data;
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}