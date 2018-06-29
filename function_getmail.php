<?php
/**
 *用于收取邮箱中的信件，目前只支持pop3协议
 * @filename function_getmail.php
 * @touch date Tue 22 Apr 2009 20:49:12 AM CST
 * @package Get_Ganji_test_mail
 * @author zhangyufeng
 * @version 1.0.0
 * @copyright (c) 2009, zhangyufeng@staff.ganji.com
 */

header("content-type:text/html;charset=utf-8");
//function ganji_get_test_mail($host, $port, $user, $password, $checkmail, $saveFile)
ini_set('memory_limit', '3072M');    // 临时设置最大内存占用为3G
set_time_limit(0);   // 设置脚本最大执行时间 为0 永不过期

function ganji_get_test_mail($array_values)
{
    $host = $array_values['host'];
    $port = $array_values['port'];
    $user = $array_values['user'];
    $password = $array_values['password'];
    $checkmail = $array_values['checkmail'];
    $saveFile = $array_values['saveFile'];
    $msg = '';
    $return_msg = '';
    if (!($sock = fsockopen(gethostbyname($host), $port, $errno, $errstr)))
        exit($errno . ': ' . $errstr);
    set_socket_blocking($sock, true);

    $command = "USER " . $user . "\r\n";
    fwrite($sock, $command);
    $msg = fgets($sock);
    echo $msg;
    $command = "PASS " . $password . "\r\n";
    fwrite($sock, $command);
    $msg = fgets($sock);
    echo $msg;


    $command = "stat\r\n";
    fwrite($sock, $command);
    $return_msg = fgets($sock);

    $msg = fgets($sock);
    echo $msg;

    $command = "LIST\r\n";
    fwrite($sock, $command);
    $all_mails = array();
    while (true) {
        $msg = fgets($sock);
        if (!preg_match('/^\+OK/', $msg) && !preg_match('/^\./', $msg)) {
            $msg = preg_replace('/\ .*\r\n/', '', $msg);
            array_push($all_mails, $msg);
        }
        if (preg_match('/^\./', $msg))
            break;
    }

    $ganji_mails = array();
    foreach ($all_mails as $item) {
        fwrite($sock, "TOP $item 0\r\n");
        while (true) {
            $msg = fgets($sock);
            array_push($ganji_mails, $item);
            if (preg_match('/^\./', $msg))
                break;
        }
    }
    $mail_content = '';
    $array_ganji_mails = array();
    foreach ($ganji_mails as $item) {
        fwrite($sock, "RETR $item\r\n");
        while (true) {
            $msg = fgets($sock);
            $mail_content .= $msg;
            if (preg_match('/^\./', $msg)) {
                print_r($mail_content);//exit;
                array_push($array_ganji_mails, iconv_mime_decode_headers(htmlspecialchars($mail_content), 0, "ISO-8859-1"));
                $mail_content = '';
                break;
            }
        }
    }
    $command = "QUIT\r\n";
    fwrite($sock, $command);
    $msg = fgets($sock);
    file_put_contents($saveFile, json_encode($array_ganji_mails));
    //echo $msg;
    return $return_msg;
}