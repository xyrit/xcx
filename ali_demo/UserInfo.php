<?php
require_once 'function.inc.php';
require_once 'aop/request/AlipaySystemOauthTokenRequest.php';
require_once 'aop/request/AlipayUserUserinfoShareRequest.php';

/**
 * 支付宝获取用户信息
 * Class UserInfo
 */
class UserInfo
{
    public function getUserInfo($auth_code)
    {
        $token = $this->requestToken($auth_code);
        writeLog(var_export($token, true));
        //echo '<pre/>';
        //print_r(json_decode(json_encode($token), true));
        if (isset ($token->alipay_system_oauth_token_response)) {
            $token_str = $token->alipay_system_oauth_token_response->access_token;
            $user_info = $this->requestUserInfo($token_str);

            writeLog("user_info" . var_export($user_info, true));

            if (isset ($user_info->alipay_user_userinfo_share_response)) {
                return $user_info->alipay_user_userinfo_share_response;
            } else
                return $token->alipay_system_oauth_token_response;

        } elseif (isset ($token->error_response)) {
            // 记录错误返回信息
            writeLog($token->error_response->sub_msg);
            return $token->error_response->sub_msg;
        } else
            return '';
    }

    public function requestUserInfo($token)
    {
        $AlipayUserUserinfoShareRequest = new AlipayUserUserinfoShareRequest ();
        $result = aopclient_request_execute($AlipayUserUserinfoShareRequest, $token);
        return $result;
    }

    public function requestToken($auth_code)
    {
        $AlipaySystemOauthTokenRequest = new AlipaySystemOauthTokenRequest ();
        $AlipaySystemOauthTokenRequest->setCode($auth_code);
        $AlipaySystemOauthTokenRequest->setGrantType("authorization_code");
        $result = aopclient_request_execute($AlipaySystemOauthTokenRequest);
        return $result;
    }
}