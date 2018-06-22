<?php
namespace app\pay\controller;
use think\Controller;
class Home extends Controller
{
	public function index(){
			add_log();	
			$post = input('');
			if($post['code'] == 'SUCCESS'){
				
			}else{
				
			}
			echo '支付接口';
	}
	public function refund(){
			add_log();
			echo '退款';
	}
	
}
