<?php
namespace app\dc\model\v1;
use think\Model;
use app\dc\model\v1\Dcno;
use app\dc\model\v1\Store;
use app\dc\model\v1\Muser;
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
    public function push_dc_msg($phone, $uid, $massage, $serve_mode, $status, $no, $device_tag, $role_tag)
    {
    	$this->title = '点击获取更多!';
        $rs = Db::name('token')->where(array('uid' => $uid))->find();
        if ($rs) {
            $RegistrationId = $device_tag ? $device_tag : $phone;
            // $this->api_push_msg($massage, "$no", "ok", "$RegistrationId");//1.3
        } 
        // dump($uid);
        $app_key = '74cf5522a74ab07a4442b92f';
        $master_secret = '376aab71e4322352a2b762da';
        $client = new \JPush\Client($app_key, $master_secret);
		$result = $client->push()
                ->setPlatform('all')//设置平台
                ->addRegistrationId($RegistrationId)//RegistrationId
                ->addAndroidNotification($massage, $this->title, 1, array("id" => $uid, "msg" => 'ok',"no"=>$no,'serve_mode' =>$serve_mode))//设置通知
                ->addIosNotification($massage, 'iOS sound', \JPush\Config::DISABLE_BADGE, true, 'iOS category', array("id" => $uid, "msg" => 'ok',"no"=>$no,'serve_mode' =>$serve_mode))//设置通知
				->setOptions(null,null,null,true,null)
                ->send();
                // dump($result);die;
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
            $massage = $no . '号餐桌呼叫服务！';
        } else{
            $massage =  '';
        } 
        //当前商户
        $merchants_info = Store::where(array('id' => $mid))->find();
        $uid = $merchants_info['uid'];
        $res = Muser::where(array('id' => $uid))->find();
        $user_phone = $res['user_phone'];
        $device_tag = $res['device_tag'];
        if ($user_phone) $this->push_dc_msg($user_phone, $uid, $massage, $serve_mode, $status, $no, $device_tag, '商户');
	}	    
}