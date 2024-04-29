<?php

namespace Lyz\WeChat\Contracts;

/**
 * token 缓存类
 * Class accessTokenCache
 * @package Lyz\WeChat\Contracts
 */
class accessTokenCache
{
    /**
     * token 过期时间 7200秒
     */
    const EXPIRE_TIME = 7000;

    /**
     * constructor.
     */
    public function __construct()
    {
    }

    /**
     * 写入 Token
     * 
     * @param string $token
     * @return void
     */
    public static function setToken($token)
    {
    }

    /**
     * 获取 Token
     * 
     * @return string 为空则token过期或token无效等
     */
    public static function getToken()
    {
        return '';
    }

    /**
     * 清除token
     * 
     * @return void
     */
    public static function clearToken()
    {
    }
}
