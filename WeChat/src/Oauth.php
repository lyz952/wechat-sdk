<?php

namespace Lyz\WeChat;

use Lyz\WeChat\contracts\BasicWeChat;

/**
 * Class Oauth
 * 微信网页授权
 * @package Lyz\WeChat
 */
class Oauth extends BasicWeChat
{
    /**
     * Oauth 授权跳转接口
     * 
     * @param string $redirect_url 授权回跳地址
     * @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
     * @return string
     */
    public function getOauthRedirect($redirect_url, $state = '', $scope = 'snsapi_base')
    {
        $redirect_uri = urlencode($redirect_url);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * 通过 code 获取 AccessToken 和 openid
     * 
     * @param string $code 授权Code值，不传则取GET参数
     * @return array
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function getOauthAccessToken($code = '')
    {
        /*
            GET https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
                |参数	         |类型   |是否必须 |描述
            请求:
                |appid           |string |是 |公众号的唯一标识
                |secret	         |string |是 |公众号的appsecret
                |code	         |string |是 |授权 Code
                |grant_type      |string |是 |固定填写为 authorization_code
        
            返回:
                |access_token    |string |是 |网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同
                |expires_in	     |int	 |是 |access_token接口调用凭证超时时间，单位（秒）
                |refresh_token	 |int	 |是 |用户刷新access_token
                |openid	         |int	 |是 |用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
                |scope	         |int	 |是 |用户授权的作用域，使用逗号（,）分隔
                |is_snapshotuser |int	 |否 |是否为快照页模式虚拟账号，只有当用户是快照页模式虚拟账号时返回，值为1
                |unionid	     |int	 |否 |用户统一标识（针对一个微信开放平台账号下的应用，同一用户的 unionid 是唯一的），只有当scope为"snsapi_userinfo"时返回
        */
        $code = $code ? $code : (isset($_GET['code']) ? $_GET['code'] : '');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        return $this->callGetApi($url);
    }
}
