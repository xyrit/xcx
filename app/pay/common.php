<?php
function curl_get($url, $post){
		$options = array(
				'http'=> array(
				'method'=>'POST',
				'header' => "Content-type: application/x-www-form-urlencoded ",
				'content'=> http_build_query($post),
			),
		);
		
		$result = file_get_contents($url,false, stream_context_create($options));
		return $result;
}

function getClientIP()
{
    global $ip;  
    if (getenv("HTTP_CLIENT_IP"))  
        $ip = getenv("HTTP_CLIENT_IP");  
    else if(getenv("HTTP_X_FORWARDED_FOR"))  
        $ip = getenv("HTTP_X_FORWARDED_FOR");  
    else if(getenv("REMOTE_ADDR"))  
        $ip = getenv("REMOTE_ADDR");  
    else $ip = "Unknow";  
    return $ip;  
}

function getSign($arr)
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
    $String =formatBizQueryParaMap($Parameters, false);
    //签名步骤二：在string后加入KEY
    $String = $String . "&key=" . APPKEY;
    //签名步骤三：MD5加密
    $String = md5($String);
    //签名步骤四：所有字符转为大写
    $result_ = strtoupper($String);
    return $result_;
}
/**
 * 作用：格式化参数，签名过程需要使用
 * @param $paraMap
 * @param $urlencode
 * @return string
 */
function formatBizQueryParaMap($paraMap, $urlencode)
{
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v){
        if ($urlencode){
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
function http_post($url,$post_data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}
function pc_err($msg,$a = []){
		$data['msg'] = urlencode($msg);
		$data['code'] = 'FAIL';
		if($a){
			$data = array_merge($data,$a);
		}
	//	is_array($data)) && ($data = json_encode($data));
		header("Content-type: application/json;charset=utf-8");
		add_log(json_encode($data));
		echo urldecode(json_encode($data));
		die;
}
function pc_succ($data){
		$data['code'] = 'SUCCESS';
		header("Content-type: application/json; charset=utf-8");
		//add_log(json_encode($data));
		echo json_encode($data);
		
}
