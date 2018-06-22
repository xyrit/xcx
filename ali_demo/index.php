<?php
require_once 'function.inc.php';
require_once 'AopSdk.php';
require_once 'HttpRequst.php';
require_once 'config.php';
require_once 'UserInfo.php';
require_once 'PushMsg.php';

header("Content-type: text/html; charset=utf-8");

$auth_code = HttpRequest::getRequest("auth_code");
if (!empty ($auth_code)) {
    require_once 'UserInfo.php';
    $userObj = new UserInfo ();
    $userInfo = $userObj->getUserInfo($auth_code);
    $userInfo = json_decode(json_encode($userInfo), true);
    echo '<pre/>';
    //print_r($_REQUEST);
    $param = $_REQUEST;
    if ($param['reffer_type'] == 'pay') {
        $str = '';
        $str = $param['reffer_url'] . '&m=' . $param['m'] . '&a=' . $param['a'] . '&';
        unset($param['reffer_url']);
        unset($param['a']);
        unset($param['m']);
        unset($param['reffer_type']);
        foreach ($param as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }

        $str .= 'user_info=' . json_encode($userInfo);
        print_r($userInfo);
        exit;
        header("Location: $str");
    } else {
        print_r($userInfo);
    }

}