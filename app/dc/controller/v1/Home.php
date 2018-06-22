<?php
namespace app\dc\controller\v1;
use think\Controller;
class Home extends controller
{
	protected function _initialize(){
				add_log();
				if(defined('UID')) return;
				$member_id = 0;
				if($this->request->has('token')&&$token=input('token')){
							$member_id = db('xcx_token')->where('token',$token)->value('uid');
							$member_id || err('登录信息已经过期',110);
				}
				define('UID',$member_id);
				
		    	if(!UID){
		    			err('缺少token',110);
		    	}
//    	}
	}
	protected function err($msg='',$code=404){
				  header("Content-type: text/json");
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  echo json_encode($array);
				  exit;
	}
	protected function succ($data=array(),$msg='SUCC',$code=0){
				   $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  header("Content-type: text/json");
				  echo json_encode($array);
				  exit;
	}
}
