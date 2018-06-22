<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class ScreenMemcardUse extends Model
{
		protected $createTime =  '';
		protected $updateTime = '';
		//我的会员卡
	   	public function lists($store_id,$uid){
				$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
				$where1 = 'b.mid = '.$store_id;
				$where  = ' and status = 1';
				$data = $this->query('select * from (select id,card_id,card_code,card_amount,card_balance,yue,pay_pass from   ypt_screen_memcard_use  where `unionid`= "'.$unionid.'" '.$where.') as a left join ypt_screen_memcard b  ON `a`.`card_id`=`b`.`card_id` where '.$where1 );
				// echo $this->getLastSql();
				return $data;
	   	}

	   	public function lists_two($store_id,$uid){
				$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
				$agent_id = Db::name('merchants_users')->where('id',$store_id)->value('agent_id');
				$where1 = 'b.mid IN ('.$store_id.','.$agent_id.')';
				$where  = ' and status = 1';
				$data = $this->query('select * from (select id,card_id,card_code,card_balance,yue,pay_pass,level from   ypt_screen_memcard_use  where `unionid`= "'.$unionid.'" '.$where.') as a left join ypt_screen_memcard b  ON `a`.`card_id`=`b`.`card_id` where '.$where1.' order by is_agent desc');
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
				if($data->total_price>$price){
						return $this->error('商品金额不够使用优惠券');
				}
				return $data;
		}
		public function updateuser($code,$bonus,$order_sn){
				//查询cart_id
				$card = $this->where('card_code',$code)->find();
				$ScreenMemcardLog = model('ScreenMemcardLog');
				
				$token = model('Wx')->get_token();
				
				$data['add_bonus'] = $bonus;
				$data['code'] = $code;
				$data['card_id'] = $card['card_id'];
				$data['record_bonus'] = $order_sn;
				if($bonus>0){
					$card->card_amount = $card->card_amount + $bonus;
					$card->card_balance =  $card->card_balance + $bonus;
					$data['card_balance'] = $card->card_balance;
				}else{
					$b = -$bonus;
					
					if ($card['card_balance'] >= $b) {
						$card->card_balance =  $card->card_balance + $bonus;
						$data['card_balance'] = $card->card_balance;
					}else{
						return $this->error('积分不足');
					}
				}
				//记录日志
				$ScreenMemcardLog->add($code,$bonus,$card->card_amount+$bonus,$order_sn,json_encode($data));
				file_put_contents('./data/log/testcoupon.log', date("Y-m-d H:i:s") . json_encode($data). PHP_EOL, FILE_APPEND | LOCK_EX);
				// $data['custom_field_value1'] = $yue + $user_money;
				$msg = curl_post1('https://api.weixin.qq.com/card/membercard/updateuser?access_token='.$token,json_encode($data));
				
				$ScreenMemcardLog->_update($order_sn,$msg);
				$card->save();
		}

		public function updateuser1($code,$user_money,$order_sn){
				//查询cart_id
				// file_put_contents('8.txt',$user_money);
				$card = $this->where('card_code',$code)->find();
				$token = model('Wx')->get_token();
				$yue = $card['yue'];
				$money = -$user_money;
				if ($yue >= $money) {
					$u = $yue + $user_money;
					$data['add_yue'] = $user_money;
					$data['code'] = $code;
					$data['card_id'] = $card['card_id'];
					$data['record_bonus'] = $order_sn;
					$data['custom_field_value1'] = (string)$u;
					// $data['custom_field_value1'] = urlencode($card['yue']-$money);
					$card->yue = $yue + $user_money;
					file_put_contents('./data/log/testcard.log', date("Y-m-d H:i:s") . json_encode($data). PHP_EOL, FILE_APPEND | LOCK_EX);
					$msg = curl_post1('https://api.weixin.qq.com/card/membercard/updateuser?access_token='.$token,json_encode($data));
					$card->save();
				}else{
					return $this->error('余额不足');
				}
		}
		//可以使用优惠券
	   	public function can_use($store_id,$uid,$price){
	   			$unionid = db::name('screen_mem')->where('id',$uid)->value('unionid');
	   			$merchants_id = db::name('merchants')->where('uid',$store_id)->value('id');
	   			
	   			$where1 = '1=1 and b.end_timestamp > '.time().' and b.begin_timestamp < '.time().' and b.total_price <= '.$price.' and b.mid = '.$merchants_id;
	   			$where  = ' and status = 1';
	   			$data = $this->query('select a.id,b.title,b.end_timestamp,b.begin_timestamp,b.de_price,b.total_price  from (select id,card_id from   ypt_screen_user_coupons where `unionid`= "'.$unionid.'" '.$where.') as a left join ypt_screen_coupons b  ON `a`.`card_id`=`b`.`card_id` where '.$where1);
	   		
	   			//add_log($this->getLastSql());
	   			return $data;

	   	}
	   	public function error($msg){
	   		$this->error = $msg;
	   		return false;
	   	}

	   	//新版本  核销会员卡
		public function cardOff($code,$bonus,$order_sn,$user_money=0){
				//查询cart_id
				$card = $this->where('card_code|entity_card_code',$code)->find();
				$cards = db::name('screen_memcard')->where('id',$card['memcard_id'])->find();
				//如果是代理商会员卡，给商家加上储值
				if ($user_money) {
					if ($cards['is_agent']) {
						$order = db::name('order')->where(array('order_sn'=>$order_sn))->find();
						$role_id = db::name('merchants_role_users')->where(array('uid'=>$order['user_id']))->value('role_id');
						if ($role_id==3) {//商家
							//先增加余额扣掉手续费
							$card_rate = db::name('merchants_users')->where(array('id'=>$order['user_id']))->value('card_rate');
							Db::name('merchants_users')->where('id',$order['user_id'])->setInc('card_balance',$order['user_money']*$card_rate/100);
							$card_balance = db::name('merchants_users')->where(array('id'=>$order['user_id']))->value('card_balance');
							$log = array('price'=>$order['user_money']*$card_rate/100,'add_time'=>time(),'remark'=>'核销异业联盟卡','mid'=>$order['user_id'],'balance'=>$card_balance,'order_sn'=>$order_sn,'ori_price'=>$order['user_money'],'rate_price'=>$order['user_money']-$order['user_money']*$card_rate/100);
							Db::name('balance_log')->insert($log);
						}
					}
				}
				$ScreenMemcardLog = model('ScreenMemcardLog');
				if($bonus>$cards['expense_credits_max']){
                    $bonus=$cards['expense_credits_max'];
                }
                // dump($code);dump($bonus);die;
                //获取商户的等级信息,level_set等级设置，level_up是否可升级
	            if($cards['level_set']==1 && $cards['level_up']==1){
	                //获取该会员的单次消费expense_single，累计消费expense，累计积分card_amount
	                $field = 'ifnull(sum(order_amount),0) as expense,ifnull(max(order_amount),0) as expense_single';
	                $mem_info = db::name('order')->where(array('order_sn'=>$order_sn))->field($field)->find();
	                $mem_info['card_amount'] = $this->where("card_code='$code'")->value('card_amount');
	                #充值记录信息，recharge累计充值金额，recharge_single单次充值最大金额
	                $recharge_info = db::name('user_recharge')
                    ->where(array('memcard_id'=>$cards['id'],'status'=>1))
                    ->field('ifnull(sum(real_price),0) as recharge,ifnull(max(real_price),0) as recharge_single')
                    ->find();
                	$mem_info = array_merge($mem_info,$recharge_info);
	                //会员卡所有等级列表
	                $memcard_level = db::name('screen_memcard_level')->where(array('c_id'=>$cards['id']))->order('level asc')->select();
	                foreach($memcard_level as &$value){
	                    $type = explode(',',$value['level_up_type']);
	                    foreach($type as &$val){
	                        #会员当前等级信息,current_level当前等级,current_level_name当前等级名称
	                        $level = $this->get_level($val,$mem_info,$value);
	                        // dump($val);dump($mem_info);dump($value);
	                        if($level){

	                            $current_level = $level['current_level'];
	                            $current_level_name = $level['current_level_name'];
	                            break;
	                        }
	                    }
	                }
	                if ($current_level && $current_level > $card['level']) {
		                if($current_level) db::name("screen_memcard_use")->where("card_code='$code'")->setField(array('level'=>$current_level));
			            if($current_level_name) $data['custom_field_value2'] = urlencode($current_level_name);//会员卡名称
		            }
	            }
	            
	            //记录日志
				
				$token = model('Wx')->get_token();
				
				$data['add_bonus'] = urlencode($bonus); //增减积分
				$data['code'] = urlencode($code);		//卡号
				$data['card_id'] = urlencode($card['card_id']);	//卡id
				$data['custom_field_value1'] = urlencode($card['yue']-$user_money);
				//记录日志
	            if($bonus>0){
	            	$data["record_bonus"] = urlencode('消费赠送积分');//增加的积分，负数为减
	            	$ScreenMemcardLog->add($code,$bonus,$card->card_balance + $bonus,$order_sn,json_encode($data),'消费赠送积分');
	            }else{
	            	$data["record_bonus"] = urlencode('消费使用积分');//增加的积分，负数为减
	            	$ScreenMemcardLog->add($code,$bonus,$card->card_balance + $bonus,$order_sn,json_encode($data),'消费使用积分');
	            }
				if($bonus>0){
					$card->card_amount = $card->card_amount + $bonus;
				}
				$card->card_balance =  $card->card_balance + $bonus;
				//yue，会员卡余额
	            $card->yue = $card->yue - $user_money;
	            //会员卡储值
				$msg = curl_post1('https://api.weixin.qq.com/card/membercard/updateuser?access_token='.$token,urldecode(json_encode($data)));
				$result = json_decode($msg,true);
                $result['errcode']==0?$to_status=1:$to_status=0;
				$ScreenMemcardLog->_update($order_sn,$msg,$to_status);
				$card->save();
				if($bonus>0){
					file_put_contents('./data/log/dc/'.date("Y_m_").'card_coupon.log', date("Y-m-d H:i:s") .',消费赠送，会员卡code:'.$code.',请求参数:'. json_encode($data). PHP_EOL, FILE_APPEND | LOCK_EX);
            		file_put_contents('./data/log/dc/'.date("Y_m_").'card_coupon_result.log', date("Y-m-d H:i:s") .',消费赠送，会员卡code:'.$code.',返回结果:'. json_encode($msg). PHP_EOL, FILE_APPEND | LOCK_EX);
				}else{
					file_put_contents('./data/log/dc/'.date("Y_m_").'card_coupon.log', date("Y-m-d H:i:s") .',消费使用，会员卡code:'.$code.',请求参数:'. json_encode($data). PHP_EOL, FILE_APPEND | LOCK_EX);
            		file_put_contents('./data/log/dc/'.date("Y_m_").'card_coupon_result.log', date("Y-m-d H:i:s") .',消费使用，会员卡code:'.$code.',返回结果:'. json_encode($msg). PHP_EOL, FILE_APPEND | LOCK_EX);
				}
				
		}

		//获取会员当前等级信息
	    private function get_level($type,$up_info,$level_info)
	    {
	        switch ($type) {
	            case 1:
	                if($up_info['recharge_single'] >= $level_info['level_recharge_single']){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                    $level['current_level_discount'] = $level_info['level_discount'];
	                    return $level;
	                }
	                break;
	            case 2:
	                if($up_info['recharge'] >= $level_info['level_recharge']){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                    $level['current_level_discount'] = $level_info['level_discount'];
	                    return $level;
	                }
	                break;
	            case 3:
	                if($up_info['expense_single'] >= $level_info['level_expense_single']){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                    $level['current_level_discount'] = $level_info['level_discount'];
	                    return $level;
	                }
	                break;
	            case 4:
	                if($up_info['expense'] >= $level_info['level_expense']){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                    $level['current_level_discount'] = $level_info['level_discount'];
	                    return $level;
	                }
	                break;
	            case 5:
	                if($up_info['card_amount'] >= $level_info['level_integral']){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                    $level['current_level_discount'] = $level_info['level_discount'];
	                    return $level;
	                }
	                break;
	            default:
	                if($level_info['level']==1){
	                    $level['current_level'] = $level_info['level'];
	                    $level['current_level_name'] = $level_info['level_name'];
	                }else{
	                    $level = null;
	                }
	                return $level;
        	}
	    }
	   	
}