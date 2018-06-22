<?php
namespace app\index\model;
use think\Model;
use think\Session;
use think\Db;
class User extends Model
{
		protected $name  = 'screen_mem';
		protected $createTime =  'update_time';
		protected $updateTime = 'add_time';
		protected $auto  = ['status' => 0];
		
	   	// 验证邮箱格式 是否符合指定的域名
	    protected function checkMail($value, $rule)
	    {
	    	   return 1 === preg_match('/^\w+([-+.]\w+)*@' . $rule . '$/', $value);	   
	    }
	    protected function setPasswordAttr($value){
	    			return md5($value);
	    }
	    protected function setIpAddressAttr(){
	    			return getIp();
	    }
	    public function check($data){
	    		$map['username'] = $data['username'];
	    		$user = $this->where($map)->find();
	    		if(empty($user)){
	    			return -1;
	    		}
	    		if($user['status']==0){
	    			return -2;
	    		}
	    		if(md5($data['password']) === $user['password']){
						//$this->updateLogin($user['id']); //更新用户登录信息
						return $user['uid']; //登录成功，返回用户ID
					} else {
						return -3; //密码错误
					}
	    }
	    public function login($uid){
	    		$user = $this->find($uid);
	    	
	    		 if(empty($user) || 1 != $user['status']) {
		            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
		            return false;
       			 }
	    	    /* 更新登录信息 */
		        $data = array(
		          
		            'login'           => array('exp', '`login`+1'),
		            'last_login_time' => time(),
		            'last_login_ip'   => getIp(),
		        );
		        $this->save($data,['uid'=>$user['uid']]);
		        
		        /* 记录登录SESSION和COOKIES */
		        $auth = array(
		            'uid'             => $user['uid'],
		            'username'        => $user['nickname'],
		            'last_login_time' => $user['last_login_time'],
		        );
		        session('admin_user', $auth);
		        //session('user_auth_sign', data_auth_sign($auth));
	    }
	    /**
	     * 注册用户
	     */
	  	public function quick_reg($openid,$data){
					$uid = $this->where('openid',$openid)->value('id');
					if($uid){
						$data&&$this->where('id',$uid)->update($data);
						return $this->get_token($uid);	
					}else{
						$data['openid'] = $openid;
						$this->save($data);
						return $this->get_token($this->getLastInsID());
					}
	  	}
	  	public function info($uid=0,$fields='*'){
    		$uid || err('不存在id');
    		if(strpos($fields,',')||$fields=='*'){
    			$result = $this->where('id',$uid)->field('openid,id,memimg,nickname,realname,unionid,status')->find();
    		}else{
    			$result = $this->where('id',$uid)->value($fields);
    		}
    		return $result;
    	}
    	/**
    	 * 默认地址
    	 */
    	public function _default(){
    			return  Db::name('address')->where(['uid'=>UID,'is_default'=>1])->find();
    	}
    	/**
    	 * 地址列表
    	 */
    	public function address(){
    			return db::name('address')->where('uid',UID)->select();
    	}
    	/**
    	 * 地址更新
    	 */
    	public function address_update($data){
    				
    	}
    	/**
    	 * 默认ip
    	 */
	  	/**
	  	 * 登录
	  	 */
	  	public function get_token($uid){
	  			$time = time();
	  			$token = md5($time.$uid.rand(10000,99999));
	  			return db('xcx_token')->insert(['uid'=>$uid,'add_time'=>$time,'token'=>$token])?$token:$this->error('生成token失败');
	  	}
	  	public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  	}
	    
	   	
}