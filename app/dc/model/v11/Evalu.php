<?php
namespace app\dc\model\v1;
use think\Model;
use app\dc\model\v1\User;
class Evalu extends Model
{
	// protected $autoWriteTimestamp = true;
	protected $name  = 'dc_eval';
	// protected $createTime = '';
	// protected $updateTime = '';
	// protected $auto  = ['status' => 1];
	protected $host;
 //    public function __construct()
 //    {
 //        parent::__construct();
 //        $this->host = 'http://'.$_SERVER['HTTP_HOST'];
 //    }
	
	public function picture()
	{
		return $this->hasMany('Picture','pid');
	}

	public function mem()
	{
		return $this->hasOne('screen_mem','mem_id');	
	}

	//获取评论
	public function get_eval($mid)
	{
		$this->host = 'http://'.$_SERVER['HTTP_HOST'];
		// dump($mid);
		// $res = $this->where('mid',$mid)->find();
		$res = $this->where(array('mid'=>$mid))->order('add_time DESC')->paginate(10);
		// dump($res);die;
        // $page = $res->render();
        foreach($res as $k => $v){
            if($v['memid']){
                $mem = User::where(array('id'=>$v['memid']))->field('memimg,nickname')->find();
                $res[$k]['memimg'] = $mem['memimg']?$mem['memimg']:'';
                $res[$k]['nickname'] = $mem['nickname']?$mem['nickname']:'';
            }else{
                $res[$k]['memimg'] = '';
                $res[$k]['nickname'] = '';
            }
            if($v['img']){
                $res[$k]['img'] = explode(',',$v['img']);
                // dump($res[$k]['img']);
                foreach($res[$k]['img'] as $key => $val){
                	// dump($res[$k]['img'][$key]);
                	// echo $key .'=====>'.$val;
                    $reslust[$key] =$this->host . $val;
                }
                $res[$k]['img'] = $reslust;
                // dump($reslust);die;
            }
        }
        // dump($res);die;
        return $res;
	}	
	    
}