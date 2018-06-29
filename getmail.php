<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/6/28
 * Time: 19:30
 */

header("content-type:text/html;charset=utf-8");
ini_set('memory_limit', '3072M');    // 临时设置最大内存占用为3G
set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期
//error_reporting(0);
include('function_getmail.php');

$checkmail = 'javazyf@gmail.com';
$array_all = array();

$array_163 = array(
    "host" => 'pop.163.com',
    "port" => 110,
    "user" => 'rao-5782474@163.com',
    "password" => 'Rao@19890802',
    "saveFile" => 'result/R_163.com/'.date('Y_m_d_',time()).'content.log',
    "checkmail" => $checkmail
);

$array_sohu = array(
    "host" => 'pop3.sohu.com',
    "port" => 110,
    "user" => 'ganjizyf',
    "password" => '111221111',
    "saveFile" => 'result/R_sohu.com',
    "checkmail" => $checkmail
);

$array_qq = array(
    "host" => 'pop.qq.com',
    "port" => 110,
    "user" => '247261794',
    "password" => 'Rao@19890924',
    "saveFile" => 'result/R_qq.com/'.date('Y_m_d_',time()).'content.log',
    "checkmail" => $checkmail
);

$array_21cn = array(
    "host" => 'pop.21cn.com',
    "port" => 110,
    "user" => 'ganjizyf',
    "password" => '1111111111111',
    "saveFile" => 'result/R_21cn.com',
    "checkmail" => $checkmail
);
$array_tom = array(
    "host" => 'pop.tom.com',
    "port" => 110,
    "user" => 'ganjizyf',
    "password" => '11111111111111111',
    "saveFile" => 'result/R_tom.com',
    "checkmail" => $checkmail
);

$array_sina = array(
    "host" => 'pop.sina.com',
    "port" => 110,
    "user" => 'ganjizyf',
    "password" => 'test0122225',
    "saveFile" => 'result/R_sina.com',
    "checkmail" => $checkmail
);

$array_gmail = array(
    "host" => 'ssl://pop.gmail.com',
    "port" => 995,
    "user" => 'ganjizyf@gmail.com',
    "password" => 'test0152220',
    "saveFile" => 'result/R_gmail.com',
    "checkmail" => $checkmail
);

//array_push($array_all, $array_sohu);
array_push($array_all, $array_163);
//array_push($array_all, $array_qq);
//array_push($array_all, $array_21cn);
//array_push($array_all, $array_tom);
//array_push($array_all, $array_sina);
//array_push($array_all, $array_gmail);

$headers_string = <<<EOF
Subject: =?UTF-8?B?UHLDvGZ1bmcgUHLDvGZ1bmc=?=
To: example@example.com
Date: Thu, 1 Jan 1970 00:00:00 +0000
Message-Id: <example@example.com>
Received: from localhost (localhost [127.0.0.1]) by localhost
    with SMTP id example for <example@example.com>;
    Thu, 1 Jan 1970 00:00:00 +0000 (UTC)
    (envelope-from example-return-0000-example=example.com@example.com)
Received: (qmail 0 invoked by uid 65534); 1 Thu 2003 00:00:00 +0000

EOF;

$headers =  iconv_mime_decode_headers(htmlspecialchars($headers_string), 0, "ISO-8859-1");
//print_r($headers);

foreach ($array_all as $item) {
    echo "===============================================\r\n";
    echo "===============================================\r\n";
    echo "===============================================\r\n";
    echo "Start get {$item['host']} mail..\r\n";

    ganji_get_test_mail($item) . "\r\n";

    echo "Get {$item['host']} maili finished..\r\n";
    echo "===============================================\r\n";
    echo "===============================================\r\n";
}