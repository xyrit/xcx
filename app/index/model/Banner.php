<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Banner extends Model
{
		
	
	   	// 验证邮箱格式 是否符合指定的域名
	    public function lists($mid)
	    {
	    	 $data =  $this->where('mid',$mid)->where('img','<>','')->select();
	    	
	    	 foreach($data as &$v){
	    	 		$v['img'] = URL.$v['img'];
	    	 }
	    	 if(empty($data)){
	    	 		
	    	 		$data[0]['img'] = 'https://mp.youngport.com.cn/public/banner/default.jpg';
	    	 		$data[1]['img'] = 'https://mp.youngport.com.cn/public/banner/default.jpg';
	    	 		$data[2]['img'] = 'https://mp.youngport.com.cn/public/banner/default.jpg';
	    	 }
	    	 return $data;
	    }
	  	
	  	public function error($msg){
	  		$this->error = $msg;
	  		return false;
	  	}
	    
	   	
}