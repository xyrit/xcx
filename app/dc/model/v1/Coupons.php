<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Coupons extends Model
{
		protected $name = 'screen_coupons';
		protected $createTime =  '';
		protected $updateTime = '';
	   	public function lists($store_id=0,$uid=0){
	   			$merchants_id = db::name('merchants')->where('uid',$store_id)->value('id');
	   			$data =   $this->where('mid',$merchants_id)->where('status',3)->where('is_miniapp',2)->where(time() . '>= begin_timestamp and '. time() . ' <= end_timestamp')->where('quantity > 0')->field('status,is_miniapp,begin_timestamp,end_timestamp,quantity,up_price,de_price,total_price,title,card_id')->select();
	   			// echo $this->getLastSql();
	   			$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
	   			foreach($data as $key=>&$v){
	   					if(db::name('screen_user_coupons')->where('card_id',$v->card_id)->where('unionid',$unionid)->find()){
	   							$data[$key]->status = 6;
	   					}
	   			}
	   			return $data;
	   	}
	   	public function add($id){
	   			
	   	}
	   	public function error($msg){
	   		$this->error = $msg;
	   		return false;
	   	}
}