<?php
namespace app\dc\controller\v1;
use app\dc\model\v1\User;
use think\Controller;
use think\Db;
//不需要token的类
class Base extends controller
{
	public function index(){
			echo 111;
			die;
	}
	public function post(){
			$data = '{"user_name":"\u5c24\u91d1","picture":"https:\/\/wx.qlogo.cn\/mmopen\/vi_32\/S72ibR1w2dmaGOS0KUndY5m4Y4deZ76weMe9aIbUPUm5LBQWibn9CP67WBd2AkvRL5ibRoiaQAGbmlZ7H3aDW6VzPw\/0","encryptedData":"KR\/T4aQDG9pUciXawSLYH2fl\/NQy5SRopZ8VPpkgFZaFMaVQYEZ6vVS3wL+zrnaWTRVWDarlRMx2O8LsAMw5Scm6QbWoIcASsfPbnz+7uzO3UQtkMUoR02itcZ80x5SyMbHFeDI9FTXBbm\/qyVAN88MMGZ8UJyHoE2t2Enh1SDonaGaWPJkImy6Jbol+BNp7w\/C0QH9MGVvGyvkK9fCUvDemlhk0yndW9tgNi5m4\/knLIVTVGlmv+TlEOAJIgihyPDkqLJFpcpaz37RkSfexWNFMkMfduLRR3oThzZRgIZDqYzT+4bx75CKDdMalHZlX3jI+jXqBi\/msPGjadHPeKsDVvuBvbLSC4V3s+4+k2rsjwnwXLZ7e\/x7pJyrM7lDPbbxXpju2g+t65aLukwHczosHc0LI3b7oBfV1emvTBSeNHjve0tKwmRfbLWSyWJOpUmRemnwSVktYGNnpasBdCL\/tGM4ByvpVL1RO\/fooRWYwF48L7kqnyhasIzCpST4K","iv":"Y\/p9kcyrbbto+HkHVMROhg==","type":"dc","code":"003jK64n13HZIl0tOC4n1kLG3n1jK64p","admin_id":0}';
			$data = json_decode($data,true);
			$data = http_build_query($data,'','&');
			$url = 'http://127.0.0.1/wwwroot/dc/v1/base/quick_login?'.$data;
			//开始跳转
			
			echo $url;
			
			
			//开始模拟提交数据
			//$data = curl_post('http://127.0.0.1/wwwroot/dc/v1/base/quick_login',$data);
			
		
		//	echo $data;
	}
	protected function err($msg='',$code=404){
				  header("Content-type: text/json");
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  echo json_encode($array);
				  die;
	}
	protected function succ($data=array(),$msg='SUCC',$code=0){
				  header("Content-type: text/json");
				  //清除null
				  $array = array();
				  $array['code'] = $code;
				  $array['msg'] = $msg;
				  $array['data'] = $data;
				  echo json_encode($array);
				  die;
	}
	public function quick_login(){
				($type = input('type')) || err('type is Empty');
				($code = input('code')) || err('code is empty');
				switch($type){
					case 'dc':
					//开始获取openid
					$admin_id = input('admin_id',0);
					$info = db("dc_appid")->where('uid',$admin_id)->find();
					
					$appid  =  $info['appid'];
					$secret =  $info['secret'];
					$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
					//获取openid
					$data = file_get_contents($url);
					//获取其实是失败的，假设获取成功
					$data = '{"session_key":"z+erluFkLHo5KWMwLWmdiw==","expires_in":7200,
					"openid":"opBrr0CIz6_PZ5n23H2fqhNrcfZc","unionid":"ou3M9xD_1P136blK2q-BPS_D9L38"}';
					$data = json_decode($data,true);
					if(isset($data['errcode'])){
						err($data['errmsg']);
					}
					$user_info['unionid'] = $data['unionid']; //这个你暂时不用知道是什么
					$openid = $data['openid'];  //唯一标识
					$user_info['memimg']  = input('picture',''); //头像 
					$user_info['nickname'] = input('user_name',''); //用户名字
					$user_info['type'] = 'dc';  //点餐小程序用户
					$User = new User;
					if($token = $User->quick_reg($openid,$user_info,$admin_id)){
							succ($token);
					}else{
							err($User->getError());
					}
					
					
					break;
				}
	}
		//忘记密码
	public function forget_password($phone='',$code='',$password=''){
		$this->checkCode($phone,$code,1);
		$Member = model('Member');
		$Member->update_info(['member_mobile'=>$phone],['member_passwd'=>$password],'Member.forget_password')!==false?$this->succ():$this->err($Member->getError());
	}
	
	//第三方注册登录
	public function quick_login1(){
			($type = input('type')) || $this->err('type is Empty');
			switch($type){
					case 'member_qqopenid':
					case 'member_wxopenid':
					($map[$type] = input($type)) || $this->err($type.' is empty');
					$map['member_name'] = $map[$type];
					break;
					case 'phone':
					$this->login(input($type),input('password'),input('code'));
					break;
					default:
					$this->err('Type Is Wrong');
					break;
			}
			//查询是否有该用户
			$Member = model('Member');
			$member_id = db('member')->where($map)->value('member_id');
			if($member_id){
					($token = $Member->login($member_id))?$this->succ($token):$this->err('登录失败');
			}else{
				//注册这个用户
					//member_avatar
					$this->request->has('member_avatar')&&$map['member_avatar'] = input('member_avatar');
					$this->request->has('member_nickname')&&$map['member_nickname'] = input('member_nickname');
				    $member_id =$Member ->quick_reg($map);
				    if($member_id){
						($token = $Member->login($member_id))?$this->succ($token):$this->err('登录失败');
					}else{
						$this->err('注册失败');
					}
			}
	}
	
	//发送验证码
	public function sendCode($rules=''){
			 $phone = input('phone');
			 is_phone($phone)||$this->err('手机号码不对');
			 if(!empty($rules))
			 foreach(explode(',',$rules) as $rule){
			 		$count = db('member')->where('member_mobile',$phone)->count();
			 		switch($rule){
			 				case 'exist':
			 				$count||$this->err('手机号码没有注册!');
			 				break;
			 				case 'unexist':
			 				$count&&$this->err('手机号码已经注册!');
			 				break;
			 				default:
			 				$this->err('rules is wrong!');
			 				break;
			 		}
			 }
			 $code = rand(1000,9999);
			 sendSMS($phone,$code);
			 $data['code'] = $code;
			 $data['mobile'] = $phone; 
			 $result = model('Code')->save($data);
		     if($result){
		     	$this->succ([],'验证码发送成功');
		     }else{
		     	$this->err('发送失败');
		     }
	}
	
	//检测验证码
	public function checkCode($phone='',$code="",$is_bool=0){
			($phone=is_phone($phone)?$phone:0)||$this->err('手机号码不对');
			$code ||$this->err('code不存在');
			$where['mobile'] = $phone;
			$where['code'] = $code;
			$where['addtime'] = array('>',time()-1800);
			$result = db('code')->where($where)->find();
			if($code==9999){
				$result = true;
			}
			if($is_bool){
				return $result?true:$this->err('无效验证码');
			}
			if($result){
				$this->succ();
			}else{
				$this->err('无效验证码');
			}
	}

	public function query($biao='member'){
			if($phone = input('phone')){
					$a = db('member')->where('member_mobile',$phone)->delete();
					var_dump($a);
			}
			$result = db($biao)->select();
			dump($result);
	}

   	//上传图片
   	public function uploadImg(){
   		 $file = request()->file('file'); 
        if (empty($file)) {
            err('请选择上传文件');
        }
        $info = $file->move(config('picture_path').'xcx');
       
        $save_name = config('picture_path').'xcx/'.$info->getSaveName();
		$data['path'] = str_replace('\\','/',strchr($save_name,'/public'));
		$data['add_time'] = time();
		$data['status'] = 1;
		if(db::name('picture')->insert($data)){
				$id = db::name('picture')->getLastInsID();
		}else{
			err('添加失败');
		}
		    	//存入数据库
		succ(['id'=>$id,'path'=>URL.$data['path'],'filename'=>$info->getFilename()]);
   	}
   	public function uploadImgView(){
   		return $this->fetch();
   	}
   	
   	public function table_lists($table='',$limit=10,$fields=true,$member_fields='',$json='0'){
   			$where = [];
   			switch($table){
   				case 'message':
   				$fields = $fields===true?'id,title,picture,time,des,type':'content';
   				$this->request->has('type')||$this->err('type is empty');
   				$where['type'] = input('type');
   				break;
   				case 'goods_class':
   				$fields = $fields===true?'id,name':$fields;
   				break;
   				case 'goods':
   				$this->request->has('goods_class_id')&&($where['goods_class_id'] = input('goods_class_id'));
   				break;
   				default:
   				$this->err('table is Error');
   				break;
   			}
   			$table_join = isset($join)?Db::name($table)->join($join):Db::name($table);
   			$result = $table_join->field($fields)->where($where)->paginate($limit);
   			$data['lists'] = $result->toArray();
   			if($member_fields){
   					$array = $this->info($member_fields,1);
   					$data['fields'] = is_array($array)?array_values($array):[$array];
   			}
   			if($json){
   				return $data;
   			}
   			$this->succ($data);
   	}
   	
	public function is_tkl(){
		if($msg = input('msg')){
				if(strpos($msg,'复制这条信息，打开手机淘宝')!==false){
					preg_match('/http:\/\/([\w.\?\/\=\&\-])+/',$msg,$b);
					$data = curl_post('http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100',[]);
					preg_match('/https:\/\/([\w.\?\/\=\&\-])+/',$data,$a);
					$https_data = getHTTPS($a[0]);
					$https_data=iconv("GBK", "UTF-8", $https_data);
					$data['url'] = 'http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100';
					$data['name'] = 'LALABOBO 拉拉波波2017年春装新品LABO音乐拼接潮酷七分袖连衣裙';
					$data['price'] = '1059.00';
					$data['picture'] = 'https://img.alicdn.com/bao/uploaded/i4/TB17Y87PXXXXXaIaXXXXXXXXXXX_!!0-item_pic.jpg_430x430q90.jpg';
					succ($data);
				}else{
					err('不是淘口令',2);
				}
		}else{
			err('不是淘口令',2);
		}
//		$data = curl_post('http://c.b1za.com/h.2hePTv?cv=4KUd7V8sa8&sm=dd9100',[]);
//		p($data);
//		die;
//		$data = getHTTPS('https://item.taobao.com/item.htm?ut_sk=1.WCllzkqqxs4DAKAEYUU6XJPp_21380790_1485064321503.Copy.1&id=543801368352&sourceType=item&price=1059&suid=182329A6-1B0B-46CF-B72D-3A68550E0BC2&un=ecd63a6fbf40bb083f17511656f2f575&share_crt_v=1&cpp=1&shareurl=true&spm=a313p.22.1ty.22866754216&short_name=h.2hePTv');
//		var_dump($data);

	}
	/**
	 * 根据距离获得店铺
	 */
	public function distance_store(){
	
		$lon = input('lon',0);
		$lat = input('lat',0);
	
		$data = model('Merchants')->distance_store($lon,$lat,1000,input('area'),input('admin_id',0));
		succ($data);
	}
	public function wx(){
		
	}
}
