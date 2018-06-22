<?php
namespace app\dc\model\v1;
use think\Model;
use think\Db;
class Dcset extends Model
{
	// protected $autoWriteTimestamp = true;
	protected $name  = 'merchants_dc_set';

	//获取商家设置
	public function lists($store_id)
	{
		$mid = $this->_get_merchants($store_id);
		$dc_set = $this->where('mid',$mid)->find();
		return $dc_set;
	}

	/**
	 * 获取商家id
	 */
	public function _get_merchants($store_id)
	{
		$mid = db::name('merchants')->where('uid',$store_id)->field('id')->find();
		if (!$mid) {
			return $this->error('未找到商家');
		}
		return $mid['id'];
	}

	    
}