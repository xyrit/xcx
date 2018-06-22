<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
function p($arr){
	return dump($arr);
}
function tongyong($table,$field,$id){
		$pk = db($table)->getPk();
		return db($table)->where($pk,$id)->value($field);;
		
}
function get_model_attribute($model_id, $group = true,$fields=true){
    static $list;
    /* 非法ID */
    if(empty($model_id) || !is_numeric($model_id)){
        return '';
    }
    /* 获取属性 */
    if(!isset($list[$model_id])){
        $map = array('model_id'=>$model_id);
        $info = db('Attribute')->where($map)->field($fields)->select();
        $list[$model_id] = $info;
    }
   
    
    $attr = array();
    if($group){
        foreach ($list[$model_id] as $value) {
            $attr[$value['id']] = $value;
        }
        $model     = db("Model")->field("field_sort,attribute_list,attribute_alias")->find($model_id);
        $attribute = explode(",", $model['attribute_list']);
        
        if (empty($model['field_sort'])){ //未排序
            $group = array(1 => array_merge($attr));
        } else {
            $group = json_decode($model['field_sort'], true);
            $keys = array_keys($group);
           
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    $value[$key] = $attr[$val];
                    unset($attr[$val]);
                }
    	  	}
    	 
            if (!empty($attr)) {
                foreach ($attr as $key => $val) {
                    if (!in_array($val['id'], $attribute)) {
                        unset($attr[$key]);
                    }
                }
                $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
       	 }
        }
        
        
        if (!empty($model['attribute_alias'])) {
            $alias  = preg_split('/[;\r\n]+/s', $model['attribute_alias']);
            $fields = array();
            foreach ($alias as &$value) {
                $val             = explode(':', $value);
                $fields[$val[0]] = $val[1];
            }
            foreach ($group as &$value) {
                foreach ($value as $key => $val) {
                    if (!empty($fields[$val['name']])) {
                        $value[$key]['title'] = $fields[$val['name']];
                    }
                }
            }
        }
        $attr = $group;
    }else{
        foreach ($list[$model_id] as $value) {
            $attr[$value['name']] = $value;
        }
    }
   
    return $attr;
}
function list_sort_by($list,$field, $sortby='asc') {
   if(is_array($list)){
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sortby) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

function check_position($pos = 0, $contain = 0){
    if(empty($pos) || empty($contain)){
        return false;
    }
    //将两个参数进行按位与运算，不为0则表示$contain属于$pos
    $res = $pos & $contain;
    if($res !== 0){
        return true;
    }else{
        return false;
    }
}

function get_list_field($data, $grid,$pk='id'){

    // 获取当前字段数据
    foreach($grid['field'] as $field){
        $array  =   explode('|',$field);
        $temp  =    $data[$array[0]];
        // 函数支持
        if(isset($array[1])){
        	if(strpos($array[1],'/')){
        		 $function = explode('/',$array[1]);
        		 $temp = call_user_func($function[0],$function[1],$function[2],$temp);
        	}else{
        		 $temp = call_user_func($array[1], $temp);
        	}
        }
        $data2[$array[0]]    =   $temp;
		
	}
if(!empty($grid['format'])){
        $value  =   preg_replace_callback('/\[([a-z_]+)\]/', function($match) use($temp,$data){
        	switch($match[1]){
				case 'picture';
				$a ='<img width="50px" src="__ROOT__'.$temp.'">';
				break;
				default:
				$a = $data[$match[1]];
				break;
        	}
        	return $a;
			}, $grid['format']);
			
 }else{
      $value = implode(' ',$data2);
}
if(!empty($grid['href'])){
        $links  =   explode(',',$grid['href']);
        foreach($links as $link){
            $array  =   explode('|',$link);
            $href   =   $array[0];
            $show   =   isset($array[1])?$array[1]:$value;
			
                // 替换系统特殊字符串
            $href   =   str_replace(
                    array('[DELETE]','[EDIT]','[LIST]'),
                    array('setstatus?status=-1&ids=[id]',
                    'edit?'.$pk.'=['.$pk.']&model=[model_id]',
                    'lists?pid=[id]&model=[model_id]'),
                    $href);
                // 替换数据变量
            $href   =   preg_replace_callback('/\[([0-9a-z_]+)\]/', function($match) use($data){return $data[$match[1]];}, $href);
            $val[]  =   '<a href="'.url($href).'">'.$show.'</a>';
        }
        $value  =   implode(' ',$val);
    }
    return $value;
}
function get_table_name($model_id = null){

    if(empty($model_id)){
        return false;
    }
    return  db('model')->where('id',$model_id)->value('name');
}
function is_login(){
	 $user = session('admin_user');
	 if(empty($user)){
	 	return 0;
	 }else{
	 	 return $user['uid'];
	 }
	
}

function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
function is_administrator($uid){
	 $uid = is_null($uid)?is_login():$uid;
	 return $uid&&(intval($uid)===1);
}
function list_to_tree($array,$val=0,$id='id',$pid='pid',$child ='_child'){
		
		if(empty($array)||!is_array($array)){
			return flase;
		}
		$tree = array();
		foreach($array as $key => $v){
				$tree[$v[$id]] = &$array[$key]; 
		}
		$tree1 = array();
		foreach($array as $key=>$v){
				if($v['pid']==$val){
					$tree1[] = &$array[$key];
				}else{
					if(isset($tree[$v[$pid]])){
							$tree[$v[$pid]][$child][] = &$array[$key]; 
					}
					
				}
		}
		return $tree1;
}

function parse_field_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}
 // 分析枚举类型配置值 格式 a:名称1,b:名称2
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')){
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    }else{
        $value  =   $array;
    }
    return $value;
}
// 获取属性类型信息
function get_attribute_type($type=''){
    // TODO 可以加入系统配置
    static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL'),
        'string'    =>  array('字符串','varchar(255) NOT NULL'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'date'      =>  array('日期','int(10) NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL'),
        'select'    =>  array('枚举','char(50) NOT NULL'),
        'radio'     =>  array('单选','char(10) NOT NULL'),
        'checkbox'  =>  array('多选','varchar(100) NOT NULL'),
        'editor'    =>  array('编辑器','text NOT NULL'),
        'picture'   =>  array('上传图片','varchar(255) NOT NULL'),
        'file'      =>  array('上传附件','int(10) UNSIGNED NOT NULL'),
		'pictures'   =>  array('上传多图','varchar(255) NOT NULL'),
		'widget'    =>  array('挂件','varchar(255) NOT NULL'),   
    );
    return $type?$_type[$type][0]:$_type;
}
function getIp(){
	if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
}
function hook($hook, $params = [])
{
	\think\Hook::listen($hook, $params);
}
function get_addon_class($name, $type = 'hook', $class = null)
{ 
	
	//$name = \think\Loader::parseName($name);
	$class = \think\Loader::parseName(is_null($class) ? $name : $class, 1);
	switch ($type) {
		case 'controller':
			$namespace = "\\addons\\" . $name . "\\controller\\" . $class;
			break;
		default:
			$namespace = "\\addons\\" . $name . "\\" . $class;
	}
	
	return class_exists($namespace) ? $namespace : $namespace;
}
function addons_url($url, $param = [])
{
	
    $url = parse_url($url);

    $case = config('url_convert');
    $addons = $case ? \think\Loader::parseName($url['scheme'],1) : $url['scheme'];
    $controller = $case ? \think\Loader::parseName($url['host']) : $url['host'];
    $action = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if (isset($url['query'])){
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }
    /* 基础参数 */
    $params = array(
    		'_addons'     => $addons,
    		'_controller' => $controller,
    		'_action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数
    return url("admin/addons/execute", $params);
}
function get_cover($cover_id, $field = 'path'){
    if(empty($cover_id)){
		return __ROOT__.'/Public/szdx.png';
    }
    $picture = db('picture')->where(array('status'=>1))->getById($cover_id);
    if($field == 'path'){
        if(!empty($picture['url'])){
            $picture['path'] = $picture['url'];
        }else{
            $picture['path'] = __ROOT__.$picture['path'];
        }
    }
    return empty($field) ? $picture : $picture[$field];
}
function format_bytes($size, $delimiter = ''){
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}
function curl_post($url,$data,$ip='',$port='',$username='',$pass=''){
	$ch = curl_init();
	$headers[] = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_PROXY,$ip);
	curl_setopt($ch, CURLOPT_PROXYPORT,$port);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	if($username&&$pass){
			curl_setopt($ch,CURLOPT_PROXYUSERPWD,$username,$pass);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}
function curl_post1($url,$data,$ip='',$port='',$username='',$pass=''){
	$ch = curl_init();
	$headers[] = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
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
function httpRequst_pay($url, $post_data)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
        //显示获得的数据   
}
function getHTTPS($url) {
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_REFERER, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  $result = curl_exec($ch);
  curl_close($ch);
  return $result;
}
function get_attr($id=0){
		return 	db('attr')->where('id',$id)->value('name');;
}
function add_log($param=''){
			$Request = \think\Request::instance();
			$data['action'] = $Request->module().'/'.$Request->controller().'/'.$Request->action();
			$data['add_time'] = date('Y-m-d H:i:s');
			$data['get'] = json_encode($Request->get());
			$data['post'] = json_encode($Request->post());
			$data['param'] = $param;
			db('log')->insert($data);
} 
function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
}
function xml_to_array($xml)
{
    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches))
    {
        $count = count($matches[0]);
        $arr = array();
        for($i = 0; $i < $count; $i++)
        {
            $key= $matches[1][$i];
            $val = xml_to_array( $matches[2][$i] );  // 递归
            if(array_key_exists($key, $arr))
            {
                if(is_array($arr[$key]))
                {
                    if(!array_key_exists(0,$arr[$key]))
                    {
                        $arr[$key] = array($arr[$key]);
                    }
                }else{
                    $arr[$key] = array($arr[$key]);
                }
                $arr[$key][] = $val;
            }else{
                $arr[$key] = $val;
            }
        }
        return $arr;
    }else{
        return $xml;
    }
}
 function xmlToArray($xml){    
	        //禁止引用外部xml实体
	        libxml_disable_entity_loader(true);
	        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
	        return $values;
}