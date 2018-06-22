<?php
namespace app\index\model;
use think\Model;
use think\Db;
class ScreenMemcardLog extends Model
{
		protected $createTime ='add_time';
		protected $updateTime ='update_time';
		//我的会员卡
	   	public function add($code,$value,$balance,$order_sn,$ts,$record_bonus){
			return $this->save(['code'=>$code,'value'=>$value,'balance'=>$balance,'order_sn'=>$order_sn,'record_bonus'=>$record_bonus,'ts'=>$ts]);
	   	}
	   	
	   	public function _update($order_sn,$msg,$ts_status){
			return $this->where('order_sn',$order_sn)->update(['msg'=>$msg,'ts_status'=>$ts_status]);
	   	}
	  
	   	public function error($msg){
	   		$this->error = $msg;
	   		return false;
	   	}
	   	
}