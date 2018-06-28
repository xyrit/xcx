<?php
namespace app\index\controller;
use think\Controller;

/**
 * Class Indexgit 
 * @package app\index\controller
 */
class Index extends Controller
{
   	public function push($os='iOS',$registrationId=0,$msg = '便利淘欢迎您'){
   			$app_key = '7487c0b8080843d9a092a693';
        	$master_secret = 'd2f62c2b059aabe80cc5cf71';
		    $client = new \JPush\Client($app_key, $master_secret);
			$ios_notification = array(
	            'sound' => 'hello jpush',
	            'badge' => 2,
	            'content-available' => true,
	            'category' => 'jiguang',
	            'extras' => array(
	              	    "url"=>'',
	                    "desURL"=>"http://www.sina.com.cn",
	                    "text"=>'',
	                    "title"=>"猜你喜欢"
	            )
	        );
			if(stristr($os,'iOS')){
						$a = $client->push()->setPlatform('ios');
						$b = $registrationId ==0?$a->addAllAudience():$a->addRegistrationId($registrationId);
						$result = $b->iosNotification($msg)->send();
			}else{
						
			}
			if($result['http_code']==200){
				succ();	
			}
   	}
   	public function index(){
   			
   			($store_id = input('store_id',0)) || err('store_id is empty');
			$banner = model('Banner')->lists($store_id);
    		$group = model('group')->lists($store_id);
    		$group_id = empty($group)?0:$group[0]['group_id'];
    		//获取所有商品
    		($store_id = input('store_id')) || err('store_id is mepty');
    		$lists = model('goods')->lists($store_id,$group_id);
    		$data['banner'] = $banner;
    		$data['cat_lists'] = $group;
    		$data['lists'] = $lists;
    		$data['group_id'] = $group_id;
    		//获取店铺信息
    		$data['store_info'] = model('merchants')->info($store_id);
    		succ($data);
  	}
    /**
     * 热门商品
     * @return [type] [description]
     */
    public function hot(){
        
        ($store_id = input('store_id',0)) || err('store_id is empty');
      $banner = model('Banner')->lists($store_id);
        //获取所有商品
        $lists = model('goods')->sell_hot($store_id);
        $data['banner'] = $banner;
        $data['lists'] = $lists;
        //获取店铺信息
        $data['store_info'] = model('merchants')->info($store_id);
        succ($data);
    }

    /**
     * 分类商品
     */
    public function classify(){
        ($store_id = input('store_id',0)) || err('store_id is empty');
        $group = model('group')->lists($store_id);
        $group_id = empty($group)?0:$group[0]['group_id'];
        //获取所有商品
        $lists = model('goods')->lists($store_id,$group_id);
        $data['cat_lists'] = $group;
        $data['lists'] = $lists;
        $data['group_id'] = $group_id;
        succ($data);
    }

    /**
     * 分类商品
     */
    public function classify1(){
        ($store_id = input('store_id',0)) || err('store_id is empty');
        $group = model('group')->lists($store_id);
        $group_id = empty($group)?0:$group[0]['group_id'];
        //获取所有商品
        $lists = model('goods')->lists2($store_id,$group_id);
        $data['cat_lists'] = $group;
        $data['lists'] = $lists;
        $data['group_id'] = $group_id;
        succ($data);
    }
   	public function gj($url,$ip){
   					list($ip,$port) = explode(':',$ip);
   					$data = curl_post('http://www.yj251.com/wap/index/index',[],'49.75.203.7',8998);
   					if($data){
   							succ($data);
   					}else{
   							err($data);
   					}
   	}
   
		//encryptedData解密
    public function decode()
    {
        ($admin_id = input('admin_id')) || err('admin_id为空');
        ($iv = input('iv')) || err('iv为空');
        ($encryptedData = input('encryptedData')) || err('encryptedData为空');
        ($code = input('code')) || err('code为空');
        $info = db("appid")->where('uid',$admin_id)->find();
        $appid  =  $info['appid'];
        $secret =  $info['secret'];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?=APPID&secret=SECRET&js_code=JSCODE&=';
        $dat = curl_post($url,['appid'=>$appid,'secret'=>$secret,'js_code'=>$code,'grant_type'=>'authorization_code']);
        $dat = json_decode($dat,true);
        $sessionKey = $dat['session_key'];
        $a  = include('./extend/wx/wxBizDataCrypt.php');
        spl_autoload_unregister('think\\Loader::autoload');
        $pc = new \WXBizDataCrypt($appid, $sessionKey);
        spl_autoload_register('think\\Loader::autoload');
        if($encryptedData&&$iv){
            $errCode = $pc->decryptData($encryptedData, $iv, $data);
            dump($data);
        }
    }
}
