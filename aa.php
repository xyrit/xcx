<?php 
var_dump(curl_post1('https://4399.com',''));
function curl_post1($url,$data,$ip='',$port='',$username='',$pass=''){
	$ch = curl_init();
	$headers[] = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
?>