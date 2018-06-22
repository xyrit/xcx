<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class User extends Model
{
		protected $name  = 'screen_mem';
		protected $createTime = 'add_time';
		protected $updateTime = '';
		protected $auto  = ['status' => 1];
	 	 /**
	     * 注册用户
	     */
	  	public function quick_reg($openid,$data,$admin_id){
	  				//判断是否存在这个用户
					$uid = $this->where('openid',$openid)->value('id');
					if($uid){
						//更新数据
						$data&&$this->where('id',$uid)->update($data);
						return $this->get_token($uid);	
					}else{
						$data['openid'] = $openid;
						//添加数据
						$this->save($data);
						return $this->get_token($this->getLastInsID());
					}
	  	}
	  	//token
	  	//用户请求接口的授权key，每个用户的key不一样，有时效性（这个看需求）
	  	public function get_token($uid){
	  			$time = time();
	  			$token = md5($time.$uid.rand(10000,99999));
	  			return  db('xcx_token')->insert(['uid'=>$uid,'add_time'=>$time,'token'=>$token])?$token:$this->error('生成token失败');
	  	}
	  	public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  	}
	  	/**
    	 * 地址列表
    	 */
    	public function address(){
    			return db::name('address')->where('uid',UID)->order('id desc')->select();
    	}
	    
}