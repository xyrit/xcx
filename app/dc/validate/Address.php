<?php
namespace app\common\validate;
use think\Validate;
class Address extends Validate{
		protected $rule = [
			['name','require','收货人为空'],
	   		['tel','require|tel','手机号码为空|手机号码不对'],
	   		['addresses','require','门牌号为空'],
	   		// ['address','require','地址为空'],
	   		['detail_address','require','详细地址为空'],
	   		['uid','require','用户id为空'],
	   		['lon','require','维度为空'],
	   		['lat','require','经度为空']
	   		// ['address','require|min:6','收货地址为空']
   		];
   		protected $scene = [
        	'update'  =>  ['member_mobile'],
        	'forget_password' =>['member_passwd'],
    	];
   		protected function tel($value)
	    {
	        return 1 === preg_match('/^1[34578]{1}\d{9}$/', $value);
	    }

}
