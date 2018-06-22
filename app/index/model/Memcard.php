<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Memcard extends Model
{
		protected $name = 'screen_memcard';
		protected $createTime =  '';
		protected $updateTime = '';
		//查询会员卡
	   	public function lists($store_id=0,$uid=0){
	   			return  $this->where('mid',$store_id)->where('cardstatus',4)->find();
	   	}
	   	
	   	public function error($msg){
	   		$this->error = $msg;
	   		return false;
	   	}

	   	//判断是否满足投放规则
	   	public function check($store_id,$order_id)
	   	{
	   		$card = $this->lists($store_id);
	   		$screen_cardset = Db::name('screen_cardset')->where('c_id',$card['id'])->find();
	   		if ($screen_cardset&&$screen_cardset['delivery_rules']=='1'&&$screen_cardset['delivery_xcx']=='1') {
	   			$total_amount = Db::name('order')->where('order_id',$order_id)->value('total_amount');
	   			if($total_amount >= $screen_cardset['delivery_cash']){
	   				return true;
	   			}else{
	   				return false;
	   			}
	   		}else{
	   			return false;
	   		}
	   		
	   	}
}