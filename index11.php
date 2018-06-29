<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 应用入口文件 ]
header('Access-Control-Allow-Origin: *');
// 定义应用目录
define('APP_PATH', __DIR__ . '/app/');
define('URL','http://sy.youngport.com.cn/');
define('__ROOT__',rtrim(dirname($_SERVER['SCRIPT_NAME']),DIRECTORY_SEPARATOR));
var_dump($_SERVER);
$v = explode('/',$_SERVER['PATH_INFO'])[2];
if($v=='v1')
		define('VERSION',$v);

// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';


