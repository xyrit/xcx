<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Store as StoreModel;
use app\dc\model\v1\Message_dc;
use app\dc\model\v1\Dcno;
use app\dc\model\v1\Dcset;
use app\dc\model\v1\Muser;
use app\dc\model\v1\Evalu;
use think\Controller;
use think\Db;
class Store extends Home
{
	/**
	 * 店铺首页
	 *
	 */
	public function index()
    {
    	 //检测mid is empty
    	  ($mid = input('store_id')) || err('mid is empty');
           //店铺信息
    	  $res = StoreModel::where('id',$mid)->field('merchant_name,merchant_jiancheng,logo_url,base_url,is_open')->find();
          // dump($res);die;
          // echo $res->mset->work;die;
    	  if($res){succ($res);}else{err('没有该店铺');}
    }

    /**
     * 呼叫服务
     */
    public function add_dc_message(Message_dc $mdc)
    {
        $uid = input("uid"); //商家uid
        $no = input("no");  //餐桌编号
        $store = new StoreModel;
        $mid =  $store->_get_mch_id($uid); //商户id
        // dump($mid);dump($no);die;
        $no_id = Dcno::where(array("mid"=>$mid,"no"=>$no))->find();
        // dump($no_id);die;
        if (!$no_id) err('参数错误');
        $data = array();
        $data['no_id'] = $no_id['id'];
        $data['uid'] = 26;
        $data['serve_mode'] = input('serve_mode');
        // dump($data);die;
        $result = Message_dc::create($data);
        $id = $result->id;
        // dump($result);dump($id);die;
        if ($result) {
            //推送消息
            $message = $mdc->push_message($id);
            $res =Message_dc::where("id='$id'")->find();
            succ($res);
        }else{
            // $message = $mdc->push_message($id);
            err('呼叫失败');       
        }
    }

    /**
     * 商家介绍详情
     */
    public function store_details(Evalu $eval)
    {
  		($mid = input('mid')) || err('mid is empty');
        $merchants = StoreModel::where('id',$mid)->find();
        // echo StoreModel::getLastSql();
        if(!$merchants){
            err('商户不存在');  
        }
        $address = $merchants['address'];//获取地址
        $uid = $merchants['uid'];
        // (!$merchants) || err('商户不存在');
        $muser = Muser::where('id',$uid)->find(); //获取手机
        // dump($mid);
        $dcset = Dcset::where('mid',$mid)->find(); //商家设置信息
        // echo Dcset::getLastSql();
        // dump($dcset['start_time']);die;
        $comment = $eval->get_eval($mid);  //获取点评
        // dump($comment);die;
        $eval = array();
        foreach ($comment as $key => $value) {
            $eval[$key]['memimg'] = $comment[$key]['memimg'];
            $eval[$key]['nickname'] = $comment[$key]['nickname'];
            $eval[$key]['star'] = $comment[$key]['star'];
            $eval[$key]['eval'] = $comment[$key]['eval'];
            $eval[$key]['img'] = $comment[$key]['img'];
        }
        // dump($eval);die;
        $data = array();
        $data['img']= explode(',',$dcset['img']);
        $data['address'] = $address;
        $data['phone'] = $muser['user_phone'];
        $data['avg_pay'] = $dcset['avg_pay'];
        $data['time'] = $dcset['start_time']. '-' .$dcset['end_time'];
        $data['introduce'] = $dcset['introduce'];
        $data['commment'] = $eval;
        succ($data);
    }
}
