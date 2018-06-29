<?php
/**
* 	配置账号信息
*/

class WxPayConf_pub
{
    //=======【微信支付服务商配置】=====================================
	const APPID = 'wxa8931b5133353d5c';
	const MCHID = "1485787132";
	const KEY = '2w098ry34ow5thgosdlgjsd9843wthlh';
	const APPSECRET = '278e3b3d351b1b3a5a26acd597b8af8e';
    //=========================================================

	const SUB_APPID = 'wxa8931b5133353d5c';//子商户的微信公众号appid
	const SUB_APPSECRET = '278e3b3d351b1b3a5a26acd597b8af8e';//子商户的微信公众号密匙
	//受理子商户ID，身份标识仅供测试，实际从数据库获取
	const SUB_MCHID = '1485787132';
	//=======【JSAPI路径设置】===================================
	//获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
	const JS_API_CALL_URL = 'https://sy.youngport.com.cn/index.php?s=Pay/Szlzpay/wxpay';
	const TWO_API_CALL_URL = 'https://sy.youngport.com.cn/index.php?s=Pay/Szlzpay/two_wxpay';
	const PHONE_API_CALL_URL = 'https://sy.youngport.com.cn/index.php?s=Pay/Szlzpay/wx_pay';

	//=======【证书路径设置】=====================================
	//证书路径,注意应该填写绝对路径
	const SSLCERT_PATH = '/cert/apiclient_cert.pem';
	const SSLKEY_PATH = '/cert/apiclient_key.pem';

	//=======【异步通知url设置】===================================
	//异步通知url，商户根据实际开发过程设定
    const NOTIFY_URL = 'https://sy.youngport.com.cn/notify/szlzwxpay_notify.php';

	//=======【微信接口地址】===================================
	const PAY_BACK_URL = 'https://api.mch.weixin.qq.com/secapi/pay/refund'; // 退款
	const REVERSE_URL = 'https://api.mch.weixin.qq.com/secapi/pay/reverse'; // 撤销订单
	const ORDER_QUERY_URL = 'https://api.mch.weixin.qq.com/pay/orderquery'; // 订单查询
	const MICROPAY_URL = "https://api.mch.weixin.qq.com/pay/micropay"; // 撤销订单
	const BILL_URL = "https://api.mch.weixin.qq.com/pay/downloadbill"; // 账单地址

	//=======【curl超时设置】===================================
	//本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
	const CURL_TIMEOUT = 30;
}