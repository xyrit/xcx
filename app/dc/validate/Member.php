<?php
namespace app\Wap\Validate;
use think\Validate;
class Member extends Validate{
		protected $rule = [
	   		['username','require|unique:member','手机号码为空|手机号码已注册'],
	   		['password','require|min:6','密码为空|手机密码最少6位字符']
   		];
   	 	protected $scene = [
        	'update'  =>  ['username'],
        	'forget_password' =>['password'],
    	];

}
