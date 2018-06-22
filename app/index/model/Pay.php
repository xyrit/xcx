<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Pay extends Model
{
		protected $createTime =  '';
		protected $updateTime = '';
		public function create_sign($order_amount=0,$body='',$out_trade_no=''){
			  //全局引入微信支付类
			  
	       $a =  Vendor('Wxpay.appWxPayPubHelper.WxPayPubHelper');
	        //========使用统一支付接口，获取prepay_id============
	        //使用统一支付接口
	        $unifiedOrder = new \UnifiedOrder_pub();
	        $money = $order_amount;
	        $price = $money * 100; //发送给微信服务器的价格要乘上100
	        $unifiedOrder->setParameter("body", $body); //商品描述
	        $unifiedOrder->setParameter("out_trade_no", $out_trade_no); //商户订单号
	        $unifiedOrder->setParameter("total_fee", (int)$price); //总金额
	        $unifiedOrder->setParameter("notify_url", \WxPayConf_pub::NOTIFY_URL); //通知地址
	        file_put_contents("./data/weixinLog/".date ('Y-m-d')."order.log", date ('Y-m-d H:i:s').":".\WxPayConf_pub::NOTIFY_URL.PHP_EOL, FILE_APPEND);
	        $unifiedOrder->setParameter("trade_type", "APP"); //交易类型
	        file_put_contents("./data/weixinLog/".date ('Y-m-d')."order.log", date ('Y-m-d H:i:s').":".var_export($unifiedOrder,true).PHP_EOL, FILE_APPEND);
	        //非必填参数，商户可根据实际情况选填
	        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
	        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
	        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
	        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
	        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
	        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
	        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
	        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
	        $unifiedOrderResult = $unifiedOrder->getResult(); //获取统一支付接口结果
	        file_put_contents("./data/weixinLog/".date ('Y-m-d')."order.log", date ('Y-m-d H:i:s').":".var_export($unifiedOrderResult,true).PHP_EOL, FILE_APPEND);
	        //商户根据实际情况设置相应的处理流程
	        if ($unifiedOrderResult["return_code"] == "FAIL") {
	            $this->ajaxReturn(array("通信出错：" . $unifiedOrderResult['return_msg']));
	        } elseif ($unifiedOrderResult["result_code"] == "FAIL") {
	            $this->ajaxReturn(array('status' => '2', 'message' => "错误代码：" . $unifiedOrderResult['err_code'] . "错误代码描述：" . $unifiedOrderResult['err_code_des']));
	        } elseif ($unifiedOrderResult["prepay_id"] != NULL) {
	            $data = array();
	            $data['order_sn'] = $out_trade_no; //返回订单号
	            $data['prepay_id'] = $unifiedOrderResult["prepay_id"]; //获取prepay_id
	            $data['money'] = $money;
	            $this->succ($data);
	        }
		}
	
		public function error($msg){
				$this->error = $msg;
				return false;
		}

}