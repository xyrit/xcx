<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class ScreenUserCoupons extends Model
{
		
		protected $createTime =  '';
		protected $updateTime = '';
		//我的优惠券
	   	public function lists($store_id,$uid,$status=0){
	   			$merchants_id = db::name('merchants')->where('uid',$store_id)->value('id');
	   			$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
	   			// dump($uid);
	   			$where1 = '1=1 ';
	   			switch((int)$status){
	   					case 0:
	   					$where = 'and status = 0';
	   					break;
	   					case 1:
	   					$where = 'and status = 1';
	   					$where1 .= 'and b.end_timestamp > '.time();
	   					$where1 .= ' and b.begin_timestamp < '.time();
	   					break;	
	   					case 2:
	   					$where = 'and status = 1';
	   					$where1 .= ' and b.end_timestamp < '.time();
	   					break;
	   			}
	   			$where1.=' and b.mid = '.$merchants_id.' and b.card_type = "GENERAL_COUPON"';
	   			$data = $this->query('select a.id,b.title,b.end_timestamp,b.begin_timestamp,b.de_price,b.total_price  from (select id,card_id from   ypt_screen_user_coupons where `unionid`= "'.$unionid.'" '.$where.') as a left join ypt_screen_coupons b  ON `a`.`card_id`=`b`.`card_id` where '.$where1);
	   			foreach($data as $key=> $v){
	   					$data[$key]['end_timestamp'] = date('Y-m-d',$v['end_timestamp']);
	   					$data[$key]['begin_timestamp'] = date('Y-m-d',$v['begin_timestamp']);
	   			}
	   			// p($this->getLastSql());
	   			return $data;
	   	}
	   	//检测优惠券是否可以用
		public function check($coupons_id,$uid,$store_id,$price){
				$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
				$data = $this->alias('a')->join('__SCREEN_COUPONS__ b','a.card_id = b.card_id')->where('a.id',$coupons_id)->where('a.unionid',$unionid)->field('a.id,a.usercard,a.status,b.end_timestamp,b.begin_timestamp,b.de_price as price,b.total_price')->find();
				if(empty($data)){
						return $this->error('优惠券不存在');
				}
				if($data->status==0){
						return $this->error('优惠券已经使用了');
				}
				if($data->begin_timestamp>time()){
						return $this->error('还没有到使用时间');
				}
				if($data->end_timestamp<time()){
						return $this->error('已经过期了');
				}
				// dump($price);dump($data->total_price);
				if($data->total_price>$price){
						return $this->error('商品金额不够使用优惠券');
				}
				return $data;
		}
		//可以使用优惠券
	   	public function can_use($store_id,$uid,$price){
	   			$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
	   			$merchants_id = db::name('merchants')->where('uid',$store_id)->value('id');
	   			
	   			$where1 = '1=1 and b.end_timestamp > '.time().' and b.begin_timestamp < '.time().' and b.total_price <= '.$price.' and b.mid = '.$merchants_id.' and b.card_type = "GENERAL_COUPON"';
	   			$where  = ' and status = 1';
	   			$data = $this->query('select a.id,b.title,b.end_timestamp,b.begin_timestamp,b.de_price,b.total_price  from (select id,card_id from   ypt_screen_user_coupons where `unionid`= "'.$unionid.'" '.$where.') as a left join ypt_screen_coupons b  ON `a`.`card_id`=`b`.`card_id` where '.$where1);
	   			
	   			//p($this->getLastSql());
	   			return $data;

	   	}
	   	public function error($msg){
	   		$this->error = $msg;
	   		return false;
	   	}
	   	
}