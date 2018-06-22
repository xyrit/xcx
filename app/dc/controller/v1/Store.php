<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\Store as StoreModel;
use app\dc\model\v1\Message_dc;
use app\dc\model\v1\Dcno;
use app\dc\model\v1\Dcset;
use app\dc\model\v1\Muser;
use app\dc\model\v1\Evalu;
use app\dc\model\v1\Wx;
use think\Controller;
use think\Db;
class Store extends Home
{
    protected $host;
	/**
	 * 店铺首页
	 *
	 */
	public function index()
    {
    	  //检测mid is empty
    	  ($mid = input('store_id')) || err('store_id is empty');
        $no_id = input('no_id');
        //餐桌
        $no = Dcno::where('id',$no_id)->field('no')->find();
        //店铺信息
        $user_phone = model('Muser')->where('id',$mid)->field('user_phone')->find();
    	  $res = StoreModel::where('uid',$mid)->field('id,merchant_name,merchant_jiancheng,logo_url,base_url,is_open')->find()->toArray();
    	  $set = db('merchants_dc_set')->where('mid',$res['id'])->field('serve_mode,work,start_time,end_time,is_serve,star,ps_price,qs_price,is_wm')->find();
        $res['user_phone'] = $user_phone['user_phone'];
    	  $set['serve_mode'] = explode(',',$set['serve_mode']);
        $res['logo_url'] = $res['logo_url']?$res['logo_url']:URL.'public/images/default.png';
    	  foreach($set['serve_mode'] as $k=>$v){
    	  		$set['serve_mode'][$k] = ['name'=>$v];
    	  }
        //优惠劵折扣状态
        $coupon = model('coupons');
        $data = $coupon->lists($mid,UID);
        $coupons = array();
        foreach ($data as $key => $value) {
          $coupons['coupons'][$key] = $value;
        }
        // dump($coupons);
    	  $res = array_merge($res,$set,$coupons);
        //判断餐桌
        if($no){
          $res['num_id'] = $no['no'];
        }else{
          $res['num_id'] = 0;
        }
          
    	  if($res){succ($res);}else{err('没有该店铺');}
    	  
    }
    public function index1()
    {
          //检测mid is empty
          ($mid = input('store_id')) || err('store_id is empty');
          $no_id = input('no_id');
          //餐桌
          $no = Dcno::where('id',$no_id)->field('no')->find();
          
           //店铺信息
          $res = StoreModel::where('uid',$mid)->field('id,merchant_name,merchant_jiancheng,logo_url,base_url,is_open')->find()->toArray();
          $set = db('merchants_dc_set')->where('mid',$res['id'])->field('serve_mode,work,start_time,end_time,is_serve')->find();
          $set['serve_mode'] = explode(',',$set['serve_mode']);
          foreach($set['serve_mode'] as $key=>$v){
                $set['serve_mode'][$key] = ['name'=>$v];
          }
          $res = array_merge($res,$set);
          $res['num_id'] = $no['no'];
          // dump($res);die;
          // echo $res->mset->work;die;
          if($res){succ($res);}else{err('没有该店铺');}
          
    }

    /**
     * 呼叫服务
     */
    public function add_dc_message(Message_dc $mdc)
    {
        ($uid = input("uid")) || err('商家uid为空'); //商家uid
        ($no = input("no")) || err('餐桌编号为空');  //餐桌编号
        (input("serve_mode")) || err('未填写服务方式'); 
        $store = new StoreModel;
        $mid =  $store->_get_mch_id($uid); //商户id
        // dump($mid);dump($no);die;
        $no_id = Dcno::where(array("mid"=>$mid,"no"=>$no))->find();
        // dump($no_id);die;
        if (!$no_id) err('参数错误');
        $dcset = Dcset::where('mid',$mid)->find();
        if($dcset['is_serve'] == 2){
            err('未开启呼叫服务');
        }
        $data = array();
        $data['no_id'] = $no_id['id'];
        $data['uid'] = UID;
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
        $store = new StoreModel;
        $id =  $store->_get_mch_id($mid);
        $merchants = StoreModel::where('id',$id)->find();
        // echo StoreModel::getLastSql();
        if(!$merchants){
            err('商户不存在');  
        }
        $address = $merchants['address'];//获取地址
        $uid = $merchants['uid'];
        // (!$merchants) || err('商户不存在');
        $muser = Muser::where('id',$uid)->find(); //获取手机
        // dump($mid);
        $dcset = Dcset::where('mid',$id)->find(); //商家设置信息
        // echo Dcset::getLastSql();
        // dump($dcset['start_time']);die;
        // $comment = $eval->get_eval($id);  //获取点评
        // dump($comment);die;
        $this->host = 'http://'.$_SERVER['HTTP_HOST'];
        // $eval = array();
        // foreach ($comment as $key => $value) {
        //     $eval[$key]['memimg'] = $comment[$key]['memimg'];
        //     $eval[$key]['nickname'] = $comment[$key]['nickname'];
        //     $eval[$key]['star'] = $comment[$key]['star'];
        //     $eval[$key]['eval'] = $comment[$key]['eval'];
        //     // dump($comment[$key]['img']);die;
        //     $eval[$key]['img'] = $comment[$key]['img'];
        // }
        // dump($eval);die;
        $data = array();
        // dump($dcset);die;
        if (!$dcset) {
            $data['img'] = array();
            $data['avg_pay'] ='';
            $data['time'] = '00:00-00:00';
            $data['introduce'] = ' ';
        } else {
            $data['img']= explode(',',$dcset['img']);
            foreach($data['img'] as $key => $val){
                    // dump($res[$k]['img'][$key]);
                    // echo $key .'=====>'.$val;
                    $data['img'][$key] ='http://sy.youngport.com.cn' . $val;
                }
            $data['avg_pay'] = $dcset['avg_pay'];
            $data['time'] = $dcset['start_time']. '-' .$dcset['end_time'];
            if ($dcset['introduce'] == null) {
                $data['introduce'] = ' ';
            }else{
                $data['introduce'] = $dcset['introduce'];   
            }
            
        }
        
        $data['address'] = $address;
        $data['phone'] = $muser['user_phone'];
        // $data['commment'] = $eval;
        succ($data);
    }

    /**
     * 获取餐桌编号
     */
    public function dc_no()
    {
        ($no_id = input('no_id')) || err('no_id is empty');
        $no = Dcno::where('id',$no_id)->find();
        if ($no) {
            succ($no['no']);
        }else{
            err('没有该餐桌');
        }
    }

    /**
     * 获取api_ticket
     */
    public function get_ticket()
    {
        //获取openid
        $openid = db('screen_mem')->where('id',UID)->value('openid');
        // dump($openid);
        $str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
        $nonce_str = substr(str_shuffle($str),1,10);
        $access_token = model('wx')->get_token();
        
          $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=wx_card';
          $ticket = curl_post1($url,[]);
          $api_ticket = json_decode($ticket,true);
          // dump($api_ticket);
        if ($api_ticket['errcode']==0) {
          $api_ticket = $api_ticket['ticket'];
          $timestamp = time();
          $card_id=input('card_id');
          $appsecret = '6b6a7b6994c220b5d2484e7735c0605a';
          // $sortString = $nonce_str.$timestamp.$api_ticket.$card_id;
          // dump($sortString);
          
          $arr = array($card_id,$api_ticket,$nonce_str,$timestamp);//组装参数
          asort($arr, SORT_STRING);
          // dump($arr);
          $sortString = "";
          foreach($arr as $temp){
              $sortString = $sortString.$temp;
          }
          $signature = sha1($sortString);
          $data = array('signature'=>$signature,'nonce_str'=>$nonce_str,'timestamp'=>$timestamp,'card_id'=>$card_id,'api_ticket'=>$api_ticket);
          // dump($signature);=
              succ($data);
         
        } else{
          err('未获取到签名');
        }
        
    }


    //查看优惠劵
    public function check_coupons()
    {
      //检测store_id is empty
        ($store_id = input('store_id')) || err('店铺id为空');
      //查看优惠劵
      $coupon = model('coupons');
      $coupons = $coupon->lists($store_id,UID);
      foreach ($coupons as $key => $value) {
          $value['end_timestamp'] = date('Y-m-d',$value['end_timestamp']);
      }
      succ($coupons);
    }

    /**
     * 检查距离
     */
    public function check_store()
    {
        ($store_id = input('store_id')) || err('store_id is empty');
        ($lon = input('lon')) || err('lon is empty');
        ($lat = input('lat')) || err('lat is empty');
        $area_id = input('area_id');
        $data = model('merchants')->check_store($store_id,$lon,$lat,$area_id);
        succ($data);
    }

    /**
     * 提交预约记录
     */
    public function add_yu(Message_dc $mdc)
    {
        ($eat_time = input('eat_time'))||err('用餐时间不能为空');
        ($eat_nums = input('eat_nums'))||err('用餐人数不能为空');
        ($persons = input('persons'))||err('预订人不能为空');
        ($phone = input('phone'))||err('手机号不能为空');
        ($uid = input('store_id'))||err('未获取到商家');
        $remark = input('remark');
        $data['eat_time'] = $eat_time;
        $data['eat_nums'] = $eat_nums;
        $data['persons'] = $persons;
        $data['phone'] = $phone;
        $data['remark'] = $remark;
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['uid'] = $uid;
        $data['memid'] = UID;
        // dump($eat_time);die;
        if(Db::name('dc_book')->insert($data)){
        	$bid = Db::name('dc_book')->getLastInsID();
        	$mdc->push_yu_message($bid);
            succ('预约成功,等待审核！');
        }else{
            err('预约失败');
        }
    }  

    /**
     * 查看预约记录
     */
    public function book_lists()
    {
        ($uid = input('store_id'))||err('未获取到商家');
        $memid = UID;
        $data = db('dc_book')->where(array('memid'=>$memid,'uid'=>$uid))->order('create_time desc')->select();
        if ($data) {
            succ($data);
        }else{
            err('记录获取失败');
        }
    }
}
