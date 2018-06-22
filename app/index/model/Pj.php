<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Pj extends Model
{
		protected $createTime =  'add_time';
		protected $updateTime = '';
		public function lists($goods_id){
				$data = $this->where('goods_id',$goods_id)->field('id,content,add_time,uid,star')->order('add_time desc')->paginate(10);
				foreach($data as &$v){
				 			$v['name'] = db::name('screen_mem')->where('id',$v['uid'])->value('nickname');
				 			$v['name'] = $v['name']?$v['name']:'匿名用户';
				}
				return $data;
		}
		public function news($goods_id,$limit = 1){
				$data =  $this->where('goods_id',$goods_id)->order('add_time desc')->limit($limit)->select();
				foreach($data as &$v){
				 			$v['name'] = db::name('screen_mem')->where('id',$v['uid'])->value('nickname');
				 			$v['name'] = $v['name']?$v['name']:'匿名用户';
				}
				return $data;
		}
		//添加评价
		public function add($order_id,$uid,$contents){
				//查询该订单是否存在
				if(empty($contents)){
						return $this->error('评论为空');
				}
				$order_status =  db::name('order')->where('order_id',$order_id)->value('order_status');
				if($order_status==4){
						
						$pjs = $pj = [];
						$pj['order_id'] =  $order_id;
						$pj['uid'] = $uid;
						foreach($contents as $v){
								
								list($goods_id,$content,$star) = $v;
								if(empty($content)){
											return $this->error('评论为空');
								}
								//查询该订单是否存在商品
								if(!db::name('order_goods')->where('order_id',$order_id)->where('goods_id',$goods_id)->count()){
													
														return $this->error('不存在该商品');			
								}
								//查询该商品是否评价过
								if($this->where('goods_id',$goods_id)->where('order_id',$order_id)->count()){
												continue;
								}
								//开始添加商品评价
								$pj['goods_id'] =  $goods_id;
								$pj['content'] = $content;
								$pj['star'] = $star;
								$pjs[$goods_id] = $pj;
						}
						//开启事务
						$this->startTrans();
						//添加评价
						if(!$this->saveAll($pjs)){
							$this->rollback();
							return $this->error('添加评价失败');		
						}
						//修改订单状态
						if(!db::name('order')->where('order_id',$order_id)->setField('order_status',5)){
							$this->rollback();
							return $this->error('修改订单状态失败');	
						} 
						$this->commit();
						return true;	
				}else{
					$this->error('该订单不能评价');
				}
		}
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}