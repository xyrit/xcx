<?php
namespace app\index\model;
use think\Model;
use app\index\model\Order;
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
        
        $RegistrationId = $device_tag ? $device_tag : $phone;
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
        // file_put_contents('yuying.txt',$result);  
        file_put_contents('./data/log/tui.log', date("Y-m-d H:i:s") . ' '.$phone.' 便利店<br>'. PHP_EOL, FILE_APPEND | LOCK_EX);   
        return $result;
    }

    /**
     * 推送打印订单
     */
    public function push_order_message($order_id)
    { 
        $res = Order::where(array('order_id' => $order_id))->find();
        if (!$res) return;
        $massage = '下单成功';
        $result = db::name('merchants_users')->where(array('id' => $res['user_id']))->find();
        $role = db::name('merchants_users')->where(array('pid' => $res['user_id'],'status'=>0))->select();
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
    
}