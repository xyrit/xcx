<?php
namespace app\index\controller;
use think\Db;
class Coupons extends Home
{
  	public function lists(){
  				($store_id = input('store_id')) || err('store_id is empty');
  				$coupons = model('coupons');
  				$data = $coupons->lists($store_id,UID);
  				succ($data);
  	}
  	public function my(){
  				($store_id = input('store_id')) || err('store_id is empty');
  				($status = input('status'));
				$coupons = model('ScreenUserCoupons');
				$data = $coupons->lists($store_id,UID,$status);
        // dump($data);
				succ($data);
  	}
  	
  	public function add(){
  				$id = input('id');
  				$data['timestamp'] = time();
  				$data['card'] = 102;
  				$data['token'] = '5E3E1F5E93BB089ED361A077C6B38072FB5CBE53';
  				$data['sign'] = $this->_getSign($data);
  				//var_dump($data);
  				$data = curl_post('http://sy.youngport.com.cn/index.php?s=Api/Coupon/coupon_barcode_throw',$data);
  				echo $data;
  	}
  	public function screen_memcard(){
  				
  	}
    private function getSign($arr)
    {
        //过滤null和空
        $Parameters = array_filter($arr, function ($v) {
            if ($v === null || $v === '') {
                return false;
            }
            return true;
        });
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
//        echo '【string1】' . $String . '</br>';
        //签名步骤二：在string后加入KEY
        $key = $this->key ? $this->key : \WzPayConf_pub::APPLY_KEY;
        $String = $String . "&key=" . $key;
//        echo "【string2】" . $String . "</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
//        echo "【string3】 " . $String . "</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
//        echo "【result】 " . $result_ . "</br>";
        return $result_;
    }


    /**
     *    作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = json_encode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }

        return $reqPar;
    }
    
}
