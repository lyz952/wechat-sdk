<?php

include "../vendor/autoload.php";

use Lyz\WeChat\contracts\BasicWeChat;

// 测试
// $demo = new AccessTokenDemo();
// $demo->getAccessToken();

class AccessTokenDemo
{
    public function __construct()
    {
    }

    /**
     * 公共获取 AccessToken
     * 
     * @return string
     */
    public function getAccessToken()
    {
        // 配置参数
        $config = include "./config.php";
        $config = $config['wechat'];
        // 注册代替函数
        $config['GetAccessTokenCallback'] = [AccessTokenDemo::class, 'getAccessTokenCallback'];
        return self::getAccessTokenCallback(new BasicWeChat($config));
    }

    /**
     * AccessToken 代替函数
     *
     * @return string
     */
    public static function getAccessTokenCallback(BasicWeChat $wechat)
    {
        // 获取存储的 token
        // ...

        // 验证是否过期
        if (false) {
            // 没过期返回token
            return 'AccessToken';
        } else {
            // 过期重新获取 token
            $config = [
                'appId' => $wechat->appId,
                'appSecret' => $wechat->appSecret,
            ];
            $token = (new BasicWeChat($config))->getAccessToken();

            // 更新本地存储的 token 
            // ...

            return $token;
        }
    }
}