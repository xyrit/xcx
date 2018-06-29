<?php
namespace app\pay\controller;
use think\Controller;
use think\Db;
use Lib\Subtable;
class Index extends Controller
{
	
	public function index(){

//		define("APPID","10170000142695");
//		define("APPKEY","AC644DFD284F9B6D0031611FBA19C95FCD58BF8E");
//		$url = 'https://api.youngport.com.cn/';
		$url = 'http://test.ypt5566.com/';
		$post = input('');
		yj_log('pay',$post);
		/*if(empty($post)){
				$post = '{"auth_code":"134968270664831577","bill_create_ip":"192.168.2.150","device_no":"996","goods_desc":"goods","nonce_str":"123456","pp_trade_no":"20170914163811221116090000562B","total_fee":"1","sign":"6F9F55A8C0B83E4C08AD1B944DF45DDB"}';
				$post = json_decode($post,true);
		}*/
		//$post = json_decode($post,true);
		($device_no = $post['device_no']) || pc_err('未绑定商户'); //终端设备号
		$auth_code = $post['auth_code']; //编码
		$goods_desc = $post['goods_desc'];//商品描述
		$pp_trade_no = $post['pp_trade_no'];//拍拍订单号
		$total_fee = $post['total_fee']/100;
		$ip = $post['bill_create_ip'];
		$order_sn = date("YmdHis") . rand(10000, 99999);
	//	$post = 'https://api.youngport.com.cn';
		//开始请求扫码支付
		$number = substr($auth_code, 0, 2);
		if ($number == "10" || $number == "11" || $number == "12" || $number == "13" || $number == "14" || $number == "15" && strlen($auth_code) == 18){//微信
				$pay['paystyle_id'] = 1;
				$url = $url.'api/wxpay/wxmicropay';
		}elseif($number == '28'){//支付宝
				$pay['paystyle_id'] = 2;
				$url = $url.'api/Alipay/alimicropay';
		}
		//生成订单
		$pay['merchant_id'] = db('merchants_pcsy')->where('device_no',$device_no)->value('mid');
		$pay['merchant_id']=$pay['merchant_id']?:$device_no;
		($res = db('merchants_cate')->where(array('merchant_id'=>$pay['merchant_id'],'status'=>1,'checker_id'=>0))->find()) || pc_err('未绑定商户');

		$param = [];
		switch($pay['paystyle_id']){
				case 1:
					$type = $res['wx_bank'];
					$param['merchant_id'] = $res['wx_mchid'];
					$param['key'] = $res['wx_key'];
					$pay_type = 1;					
				break;
				case 2:
					$type = $res['ali_bank'];
					$param['merchant_id'] = $res['alipay_partner'];
					$param['key'] = $res['alipay_public_key'];
					$pay_type = 2;
				break;
		}

		$param['authCode'] = $auth_code;
		$param['order_sn'] = $order_sn;
		$param['price'] = $total_fee;

		$Pay = model('pay');
		switch($type){
            /*case '4':
                $Pay = model('zspay');
                $cost_rate = db('merchants_zspay')->where('merchant_id',$pay['merchant_id'])->value('payment_type1');
                $cost_rate = $cost_rate/100;
                $result	 = $Pay->micropay($param,$pay_type);
                break;
            case '6':
                $result = $Pay->jnms_micropay($param,$pay_type);
                break;*/
            case '7':
                $cost_rate = db('merchants_xypay')->where('merchant_id',$pay['merchant_id'])->value('wx_code');
                $result = $Pay->xy_micropay($param,$pay_type);
                break;
            case '9':
                $cost_rate = '0.00';
                $Pay = model('szlz');
            //	$wx_cost_rate = db("merchants_szlzwx")->where("merchant_id=" . $pay['merchant_id'])->value("rate");
                $result =$Pay->micropay($param,$pay_type);
                break;
            case '10':
                $Pay = model('pfpay');
                $pay_type==1?$pf_rate='wx_code':$pf_rate='ali_code';
                $cost_rate = db("merchants_pfpay")->where("merchant_id",$pay['merchant_id'])->value($pf_rate);
                $result =$Pay->pf_micropay($param,$pay_type);
                break;
            case '11':
                $Pay = model('xdlpay');
                $pay_type==1?$xdl_rate='wx_rate':$xdl_rate='ali_rate';
                $cost_rate = db('merchants_xdl')->where('m_id',$pay['merchant_id'])->value($xdl_rate);
                if($pay['paystyle_id']==1){
                    $result = $Pay->wx_micropay($pay['merchant_id'],$total_fee,$auth_code,0,'',$order_sn,$cost_rate);
                }elseif($pay['paystyle_id']==2){
                    $result = $Pay->ali_micropay($pay['merchant_id'],$total_fee,$auth_code,0,'',$order_sn,$cost_rate);
                }
                break;
            case '12':
                $Pay = model('lspay');
                $is_t0 = db('merchants_leshua')->where('m_id',$pay['merchant_id'])->value('is_t0');
                if($pay_type==1){
                    $ls_rate=$is_t0==1?'wx_t0_rate':'wx_t1_rate';
                }else{
                    $ls_rate=$is_t0==1?'ali_t0_rate':'ali_t1_rate';
                }
                $cost_rate = db('merchants_leshua')->where('m_id',$pay['merchant_id'])->value($ls_rate);
                //$result = model('lspay')->micropay($param,$pay_type);
                if($pay['paystyle_id']==1){
                    $result = $Pay->wx_micropay($pay['merchant_id'],$total_fee,$auth_code,0,'',$order_sn,$cost_rate);
                }elseif($pay['paystyle_id']==2){
                    $result = $Pay->ali_micropay($pay['merchant_id'],$total_fee,$auth_code,0,'',$order_sn,$cost_rate);
                }
                break;
            default:
                pc_err('暂时没有该支付方式');
                break;
		}
        yj_log('pay',$result);
		if($result==false){
				pc_err($Pay->getError());
		}

        //add_log(json_encode($pay));
        //$pay['transaction_id'] =
        if(!in_array($type,array(11,12))){
            $pay['mode'] = 15;
            $pay['price'] = $total_fee;
            $pay['remark'] = $order_sn;
            $pay['add_time'] = time();
            $pay['bill_date'] = date('Ymd');
            $pay['status'] = 1;
            $pay['remark_mer'] = $order_sn;
            $pay['paytime'] = time();
            $pay['transId'] = $result['transaction_id'];
            $pay['bank'] = $type;
            $pay['cost_rate'] = $cost_rate;
            db(Subtable::getSubTableName('pay'))->insert($pay);
        }
        $return = [];
        $return['transaction_id'] = $order_sn;//支付订单
        $return['total_fee'] = (int)($total_fee*100);
        $return['pp_trade_no'] = $pp_trade_no;
        //WXPAY ALIPAY
        $return['pay_type'] = $pay_type==1?'WXPAY':'ALIPAY';
        //$url='http://sy.youngport.com.cn/index.php?s=Api/Cloud/printer/remark/'.$order_sn;
        //file_get_contents($url);
        //add_log($return);
        pc_succ($return);
	}

	public function refund(){
//		
//		$a = '{"bill_create_ip":"192.168.2.152","device_no":"1001","nonce_str":"123456","pp_trade_no":"2017101809543725319","refund_code":"2017101809543725319","refund_fee":"1","sign":"D693174E97BC356DD8A66951E504C79C"}';
			($remark = input("pp_trade_no")) || pc_err('订单号为空');	
			($device_no = input('device_no')) || pc_err('设备号为空');
			($price_back = input('refund_fee')) || pc_err('价格为空');
			add_log();
			//echo '退款';
			//$remark = '2017101809543725319';
			$pay['merchant_id'] = db('merchants_pcsy')->where('device_no',$device_no)->value('mid');
			$mid=$pay['merchant_id']?:$device_no;
			$url='http://sy.youngport.com.cn/index.php?s=api/base/pay_back';
			$param = [];
			$param['style'] = 2;
			$param['sign'] = '5e022b44a15a90c01';
			$param['remark'] = $remark;
			$param['mid'] = $mid;
			$param['price_back'] = $price_back;
		//	p($param);
//			$par = [];
//			foreach($param as $key=>$v){
//				$par[] = $key.'/'.$v;
//			}
		//	$url='http://sy.youngport.com.cn';
		//	$url = $url.'/'.implode($par,'/');
			//$url='http://sy.youngport.com.cn';
			//$re=file_get_contents($url);
			$param['url'] = $url;
			add_log(json_encode($param));
			$re = curl_post1('http://apiadmin.ypt5566.com/index/curl/transfer_curl',$param);
			add_log($re);
			//print_r($re);
			//add_log($re);
			$re = json_decode($re,true);
			//add_log($re['code']);
			if($re['code']=='success'){
					pc_succ(['total_fee'=>$price_back,'out_refund_no'=>$remark]);
			}else{
					pc_err('退款失败');
			}
//			$order_sn = date("YmdHis") . rand(10000, 99999);
//			define('POST_URL','http://test.ypt5566.com/');
//			add_log();
//			$post = input('');
//			if(empty($post)){
//				$post = '{"bill_create_ip":"192.168.2.151","device_no":"221116090000562B","nonce_str":"123456","pp_trade_no":"2017091817191994372","refund_code":"2017091518210995577","refund_fee":"1","sign":"1848FBE9E624FB403181E491879FEDFC"}';
//				$post = json_decode($post,true);			
//			}
//			$pay = db(Subtable::getSubTableName('pay'))->where('remark',$post['pp_trade_no'])->find();
//			//p($pay);
//			$pay || pc_err('没有查到该订单');
//			$url = POST_URL.'/Api/Trade/refundtrade';
//			$param = [
//				'orderSn'=>$pay['remark'],
//				'remark'=>$pay['remark_mer'],
//				'timestamp'=>'134920762169347590',
//				'totalAmount'=>$post['refund_fee']/100,
//				'mchId'=>APPID,
//			];
//			($mid = db::name('merchants_pcsy')->where('device_no',$post['device_no'])->value('mid')) || pc_err('商户信息为空');
//			define("APPID","10170002695368");
//			define("APPKEY","D4471A52461FATGSH080BB945D660A9A32EC1917");
//			$param['sign'] = getSign($param);
//			$data = http_post($url,$param);	
//			add_log($data);
//			$data = json_decode($data,true);
//			if($data['code']==0&&$data['data']['resultCode']=='SUCCESS'){
//					//开启事务
//							Db::startTrans();
//							try{
//									if($pay['id']){
//											//修改pay表的状态
//											Db::name(Subtable::getSubTableName('pay'))->where('id',$pay['id'])->setField('status',2);
//									}
//									//添加一条数据库到pay_back表
//									$pay_back = [];
//									$pay_back['merchant_id'] = $pay['merchant_id'];
//									$pay_back['paystyle_id'] = $pay['paystyle_id'];
//									$pay_back['back_pid'] = $pay_back['order_id'] = $pay['id'];
//									$pay_back['mode'] = 98;
//									$pay_back['price'] = $pay['price'];
//									$pay_back['price_back'] = $post['refund_fee']/100;
//									$pay_back['bank'] = $pay['bank'];
//									$pay_back['status'] = 5;
//									$pay_back['paytime'] = time();
//									$pay_back['bill_date'] = $pay['bill_date'];
//									$pay_back['remark'] = $order_sn;
//									$pay_back['type'] = 1;
//									//$pay_back['bill_date'] = $pay['bill_date'];
//									Db::name('pay_back')->insert($pay_back);
//									Db::commit();
//							} catch (\Exception $e){
//						  		    // 回滚事务
//							   		Db::rollback();						   	
//							}
//					
//					$return['total_fee'] = (int)($post['refund_fee']/100);
//					$return['out_refund_no'] = $post['pp_trade_no'];
//					pc_succ($return);
//			}else{
//					$return['out_refund_no'] = $post['pp_trade_no'];
//					pc_err($data['data']['message'],$return);
//			}
//		
	}
	
}
