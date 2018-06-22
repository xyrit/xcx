<?php
namespace app\pay\controller;
use think\Controller;
use think\Db;
class Order extends Controller
{
	public function index(){
			//221116090000562B
			$post = input('');
			$code = input('code');
			
			if(empty($post)){
					$post = '{"bill_begin_time":"'.$code.'","bill_create_ip":"192.168.2.151","bill_end_time":"20170920161143","details":"TRUE","device_no":"11","nonce_str":"123456","sign":"3B09C49BCF4E697B57E58C17670D7FA4"}';
					$post = json_decode($post,true);
			}
			
			//查询商户
			$mid = db::name('merchants_pcsy')->where('device_no',$post['device_no'])->value('mid');
			$mid=$mid?:$post['device_no'];
			
			$post['bill_begin_time'] = strtotime($post['bill_begin_time']);
			$post['bill_end_time'] = strtotime($post['bill_end_time']);
//			$lists = Db::name('pay')
//					  ->where('status','in','1,5')
//					  ->where('mode',15)
//					  ->select();
					  //price_back
			$field = 'paytime,merchant_id,status,paystyle_id,remark,price';
			$field1 = 'paytime,merchant_id,if(status=5,99,0),paystyle_id,remark,price_back as price';
			//echo 111;
			//,,,,,price,paystyle_id,mode,status,remark,price
			$lists = Db::field($field)
			      ->table('ypt_pay')
			      //->union('SELECT '.$field.' FROM ypt_pay where mode = 15 and merchant_id = 1')
			      ->union('SELECT '.$field1.' FROM ypt_pay_back where merchant_id = '.$mid.' and paytime>'.$post['bill_begin_time'].' and paytime<'.$post['bill_end_time'].' order by paytime desc')
			      ->where('mode',15)
			      ->where('merchant_id',$mid)
			      ->where('status','in','1,2')
			      ->where('paytime','gt',$post['bill_begin_time'])
				  ->where('paytime','lt',$post['bill_end_time'])
				  ->select();
		//	p($lists);
			add_log(Db::name('ypt_pay')->getLastSql());
//		p($lists);
//	die;
			$detail = $details = [];
			$total_pay_amt = $total_pay_count = $total_refund_amt = $total_refund_count =  0;
			$wx_pay = 0;
			$ali_pay = 0;
			$wx_count = 0;
			$ali_count = 0;
			$wx_re_count = 0;
			$ali_re_count = 0;
			$wx_re_pay = 0;
			$ali_re_pay = 0;
			foreach($lists as $v){
					if($v['status']==99){
						$detail['pay_status'] = 3;
						$total_refund_amt += $v['price']*100;
						$total_refund_count++;
						if($v['paystyle_id']==1){
								$wx_re_pay  += $v['price']*100;
								$wx_re_count++;
						}else{
								$ali_re_pay += $v['price']*100;
								$ali_re_count++;
						}
					}else{
						if($v['paystyle_id']==1){
								$wx_pay  += $v['price']*100;
								$wx_count++;
						}else{
								$ali_pay += $v['price']*100;
								$ali_count++;
						}
						$total_pay_amt += $v['price']*100;
						$total_pay_count++;
						$detail['pay_status'] = 2;
					}
					$detail['pay_type'] = $v['paystyle_id']==1?'WXPAY':"ALIPAY";
					$detail['refund_fee'] = (int)($v['price']*100);
					$detail['time_end'] = date('YmdHis',$v['paytime']);
					$detail['total_fee'] = (int)($v['price']*100);
					$detail['transaction_id'] = $v['remark']?:' ';
					$details[] = $detail; 
			}
			
			add_log(Db::name('pay')->getLastSql());
//			p($lists);
//			die;
			//p($post);
			$return = [
				'code'=>'SUCCESS',
				'transaction_detail' => $details,
				'pay_summary' => [
						['pay_type'=>'WXPAY','total_pay_count'=>$wx_count,'total_pay_tee'=>$wx_pay],
						['pay_type'=>'ALIPAY','total_pay_count'=>$ali_count,'total_pay_tee'=>$ali_pay]
				],	
				'refund_summary'=>[
						['pay_type'=>'WXPAY','total_refund_count'=>$wx_re_count,'total_refund_tee'=>$wx_re_pay],
						['pay_type'=>'ALIPAY','total_refund_count'=>$ali_re_count,'total_refund_tee'=>$ali_re_pay]
				],
				'total_pay_amt'=>$total_pay_amt,
				'total_pay_count'=>$total_pay_count,
				'total_refund_amt'=>$total_refund_amt,
				'total_refund_count'=>$total_refund_count,
				'print'=>'11111'
			];
			
			add_log(json_encode($return));
			header("Content-type: application/json");
			echo json_encode($return);
	}
	public function tj(){
			add_log();
			echo '账单汇总';
	}
	//查询订单
	public function query(){
			add_log();
			echo '查询订单';
			
	}
	//撤单
	public function cancel(){
			add_log();
			echo '撤销订单';
	}
	
}
