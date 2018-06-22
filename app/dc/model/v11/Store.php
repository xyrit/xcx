<?php
namespace app\dc\model\v1;
use think\Model;
class Store extends Model
{
	protected $name  = 'merchants';
	protected $createTime = '';
	protected $updateTime = '';
	protected $auto  = ['status' => 1];
 	 /**
     * 店铺信息
     */
    public function info($store_id){
    	return $this->where('id',$store_id)->find();
    }
  	public function error($msg){
  		$this->error = $msg;
  		return false;
  	}

	/**
     * 获取商家ID
     * @Param uid 商家uid
     */
    public function _get_mch_id($uid)
    {
        $id = $this->where(array('uid'=>$uid))->find();
        $id = $id['id'];
        return $id;
    }

    /**
     * 关联商家设置
     */
    public function mset()
    {
        return $this->hasOne('merchants_dc_set','id','mid');    
    }
	    
}