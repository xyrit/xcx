<?php
namespace Wzpay;
/**
 *    配置账号信息
 */
class WzPayConf_pub
{
    //=======【基本信息设置】=====================================
    //洋仆淘公众号
    const APPID = 'wx3fa82ee7deaa4a21';
    //商户号(洋仆淘自己的商户号)
//    const MCHID = '107584000030001';
    //进件的KEY
    const APPLY_KEY = 'youngPort4a21';
    //交易默认的KEY
//    const ORDER_KEY = '326545';

    //=======【商户入驻】=====================================
    //正式环境
    const APPLY_URL = 'https://svrapi.webank.com/wbap-bbfront/ImportMrch';
    //测试环境
//    const APPLY_URL = 'https://test-svrapi.webank.com/l/wbap-bbfront/ImportMrch';
    //=======【商户入驻】=====================================

    //=======【提交订单】=====================================
    //正式环境
    const POST_ORDER = 'https://svrapi.webank.com/wbap-bbfront/AddOrder';
    //测试环境
//    const POST_ORDER = 'https://test-svrapi.webank.com/l/wbap-bbfront/AddOrder';
    //=======【提交订单】=====================================

    //=======【公众号支付订单查询】=====================================
    //正式环境
    const QUERY_ORDER = 'https://svrapi.webank.com/wbap-bbfront/GetOrderStatus';
    //测试环境
//    const QUERY_ORDER = 'https://test-svrapi.webank.com/l/wbap-bbfront/GetOrderStatus';
    //=======【公众号支付订单查询】=====================================

    //=======【刷卡支付】=====================================
    //正式环境
    const MICROPAY = 'https://svrapi.webank.com/wbap-bbfront/mao';
    //测试环境
//    const MICROPAY = 'https://test-svrapi.webank.com/l/wbap-bbfront/mao';
    //=======【刷卡支付】=====================================

    //=======【刷卡支付订单查询】=====================================
    //正式环境
    const MICROPAY_QUERYORDER = 'https://svrapi.webank.com/wbap-bbfront/mgos';
    //测试环境
//    const MICROPAY_QUERYORDER = 'https://test-svrapi.webank.com/l/wbap-bbfront/mgos';
    //=======【刷卡支付订单查询】=====================================

    //=======【刷卡支付冲正接口】=====================================
    //正式环境
    const CANCEL_ORDER = 'https://svrapi.webank.com/wbap-bbfront/ro';
    //测试环境
//    const CANCEL_ORDER = 'https://test-svrapi.webank.com/l/wbap-bbfront/ro';
    //=======【刷卡支付冲正接口】=====================================

    //=======【扫码支付下单接口】=====================================
    //正式环境
    const NATIVEPAY = 'https://svrapi.webank.com/wbap-bbfront/nao';
    //测试环境
//    const NATIVEPAY = 'https://test-svrapi.webank.com/l/wbap-bbfront/nao';
    //=======【扫码支付下单接口】=====================================

    //=======【扫码支付订单查询】=====================================
    //正式环境
    const NATIVEPAY_QUERYORDER = 'https://svrapi.webank.com/wbap-bbfront/ngos';
    //测试环境
//    const NATIVEPAY_QUERYORDER = 'https://test-svrapi.webank.com/l/wbap-bbfront/ngos';
    //=======【扫码支付订单查询】=====================================

    //=======【证书路径设置】=====================================
    //证书路径,注意应该填写绝对路径
    const SSLCERT_PATH = '/alidata/www/youngshop/simplewind/Core/Library/Vendor/Wzpay/cert/apiclient_cert.pem';
    const SSLKEY_PATH = '/alidata/www/youngshop/simplewind/Core/Library/Vendor/Wzpay/cert/apiclient_key.pem';
    //=======【证书路径设置】=====================================

    //=======【异步通知url设置】===================================
    //公众号支付回调
    const NOTIFY_URL = 'https://sy.youngport.com.cn/index.php?g=Pay&m=Barcode&a=wx_notify_return';
//    const NOTIFY_URL = 'https://sy.youngport.com.cn/index.php?s=Pay/Barcode/wx_notify_return';
    //扫码支付回调
    const NATIVE_CALL_URL = 'https://sy.youngport.com.cn/index.php?g=Pay&m=Barcode&a=wz_nativeurl';
    //支付后跳转地址
    const CALLBACK_URL = 'https://sy.youngport.com.cn/index.php?g=Pay&m=Barcode&a=index';
//    const CALLBACK_URL = 'https://sy.youngport.com.cn/index.php?s=Pay/Barcode/index';
    //=======【异步通知url设置】===================================

    //=======【curl超时设置】===================================
    //本例程通过curl使用HTTP POST方法，此处可修改其超时时间，默认为30秒
    const CURL_TIMEOUT = 30;
    //=======【curl超时设置】===================================

}

?>