<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Address extends Model
{
	
		protected $createTime =  '';
		protected $updateTime = '';
		
	   	// 验证邮箱格式 是否符合指定的域名
	 	public function _update($data){
	 		$this->isUpdate = isset($data['id']);
	 		return $this->validate(true)->save($data);
	 	}
	 	public function info($id){
	 			$data = $this->where('uid',UID)->where('id',$id)->find();
	 			return $data;
	 	}
	  	public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  	}
	  	
}