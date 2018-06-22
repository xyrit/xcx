<?php 
$post = file_get_contents("php://input");
$url = 'http://119.29.232.137/index.php/'.$_SERVER['QUERY_STRING'];
echo curl_post($url,$post);
function curl_post($url,$data,$ip='',$port='',$username='',$pass=''){
	$ch = curl_init();
	//$headers[] = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);  
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_PROXY,$ip);
	curl_setopt($ch, CURLOPT_PROXYPORT,$port);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	if($username&&$pass){
			curl_setopt($ch,CURLOPT_PROXYUSERPWD,$username,$pass);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

