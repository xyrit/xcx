<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
//不需要token的类
class System extends controller
{
	//统计评价
	public function pj(){
			$data = db()->query('select goods_id,count(1) as count  from ypt_pj group by goods_id');
			p($data);
			foreach($data as $v){
					$good = db('pj')->where('goods_id',$v['goods_id'])->where('star','gt',3)->count();
					$rate = ceil(($good/$v['count'])*100);
					db::name('goods')->where('goods_id',$v['goods_id'])->update(['pj_nums'=>$v['count'],'pj_rate'=>$rate]);
					
			}
	}
	
	
}
