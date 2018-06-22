<?php
namespace app\dc\model\v1;
use think\Model;
use app\dc\model\v1\Dcno;
use app\dc\model\v1\Store;
use app\dc\model\v1\Muser;
use app\dc\model\v1\Order;
use think\Db;
class Message_dc extends Model
{
	protected $autoWriteTimestamp = true;

	/**
     * 消息推送
     * @param $phone
     * @param $uid
     * @param $massage
     * @param $status
     * @param $no
     * @param $device_tag
     * @param $role_tag
     */
    public function push_dc_msg($phone,  $massage,  $massage_details,  $device_tag)
    {
    	$this->title = '点击获取更多!';
        // $rs = Db::name('token')->where(array('uid' => $uid))->find();
        // dump($massage_details);
        $RegistrationId = $device_tag ? $device_tag : $phone;
        // file_put_contents('2.txt',$massage);
        // file_put_contents('3.txt',$massage_details);
        // dump($device_tag); 
        // dump($phone); 
        // dump($RegistrationId);die;
        $app_key = '74cf5522a74ab07a4442b92f';
        $master_secret = '376aab71e4322352a2b762da';
        $client = new \JPush\Client($app_key, $master_secret);
		$result = $client->push()
                ->setPlatform('all')//设置平台
                ->addRegistrationId($RegistrationId)
                ->addAndroidNotification($massage, $this->title, 1, $massage_details)//设置通知
                ->addIosNotification($massage, 'iOS sound', \JPush\Config::DISABLE_BADGE, true, 'iOS category', $massage_details)//设置通知
				->setOptions(null,null,null,true,null)
                ->send();
        file_put_contents('./data/log/tui.log', date("Y-m-d H:i:s") . ' '.$phone.' 点餐<br>'. PHP_EOL, FILE_APPEND | LOCK_EX);        
        return $result;
    }


    /**
     * 呼叫服务后后推送信息
     * @param int $remark
     */
    public function push_message($id = 0)
    {
        $massage = $this->where("id='$id'")->find();
        if (!$massage) return;
        $serve_mode = $massage['serve_mode']; //服务方式
        $no_id = $massage['no_id'];//餐桌id
        $no = Dcno::where("id='$no_id'")->find();
        $mid = $no['mid'];
        $no =$no['no'];	//餐桌编号
        $status = $massage['status']; //消息状态
        if ($status == 0) {
            $massage = $no . '呼叫服务！';
        } else{
            $massage =  '';
        } 
        //当前商户
        $merchants_info = Store::where(array('id' => $mid))->find();
        $uid = $merchants_info['uid'];
        $res = Muser::where(array('id' => $uid,'status'=>0))->find();
        $role = Muser::where(array('pid' => $uid,'status'=>0))->select();
        if ($role) {
            foreach ($role as $key => $value) {
                // dump($value['device_tag']);
                $user_phone1 = $value['user_phone'];
                $device_tag1 = $value['device_tag'];
                $massage_detail = array("id" => $value['id'], "msg" => 'ok',"no"=>$no,'serve_mode' =>$serve_mode,'type' => 2);
                // dump($no);dump($serve_mode);dump($massage);
                if ($user_phone1&&$device_tag1 != 0) $this->push_dc_msg($user_phone1,  $massage,  $massage_detail,  $device_tag1);
            }
        }
        // dump($res['device_tag']);
        $user_phone = $res['user_phone'];
        $device_tag = $res['device_tag'];
        $massage_details = array("id" => $uid, "msg" => 'ok',"no"=>$no,'serve_mode' =>$serve_mode,'type' => 2);
        // dump($no);dump($serve_mode);dump($massage);
        if ($user_phone) $this->push_dc_msg($user_phone,  $massage,  $massage_details,  $device_tag);
	}

    /**
     * 推送打印订单
     */
    public function push_order_message($order_id)
    { 

        
        $res = Order::where(array('order_id' => $order_id))->find();
        if (!$res) return;
        $massage = '下单成功';
        $result = Muser::where(array('id' => $res['user_id']))->find();
        $role = Muser::where(array('pid' => $res['user_id'],'status'=>0))->select();
        if ($role) {
            foreach ($role as $key => $value) {
                $user_phone1 = $value['user_phone'];
                $device_tag1 = $value['device_tag'];
                $massage_detail = array("order_id" => $order_id, "msg" => 'ok','type' => 3);
                // dump($no);dump($serve_mode);dump($massage);
                if ($user_phone1&&$device_tag1 != 0) $this->push_dc_msg($user_phone1,  $massage,  $massage_detail,  $device_tag1);
            }
        }
        $user_phone = $result['user_phone'];
        $device_tag = $result['device_tag'];
        
        $massage_details = array("order_id" => $order_id, "msg" => 'ok','type' => 3);
        if ($user_phone) $this->push_dc_msg($user_phone,  $massage,  $massage_details,  $device_tag);
    }

    /**
     * 推送预约消息
     */
    public function push_yu_message($bid)
    {
        $book = Db::name('dc_book')->where('id',$bid)->find();
        if(!$book){
            file_put_contents('./data/log/yuyue.log', date("Y-m-d H:i:s") . ' '.$bid.' 预约推送失败<br>'. PHP_EOL, FILE_APPEND | LOCK_EX); 
            return;
        }
        $massage = '有新的预约消息';
        $result = Muser::where(array('id' => $book['uid']))->find();
        $role = Muser::where(array('pid' => $book['uid'],'status'=>0))->select();
        if ($role) {
            foreach ($role as $key => $value) {
                $user_phone1 = $value['user_phone'];
                $device_tag1 = $value['device_tag'];
                $massage_detail = array("book_id" => $bid, "msg" => 'ok','type' => 4);
                // dump($no);dump($serve_mode);dump($massage);
                if ($user_phone1&&$device_tag1 != 0) $this->push_dc_msg($user_phone1,  $massage,  $massage_detail,  $device_tag1);
            }
        }
        $user_phone = $result['user_phone'];
        $device_tag = $result['device_tag'];
        
        $massage_details = array("book_id" => $bid, "msg" => 'ok','type' => 4);
        if ($user_phone) $this->push_dc_msg($user_phone,  $massage,  $massage_details,  $device_tag);

    }
    
}