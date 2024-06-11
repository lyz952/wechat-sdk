<?php

namespace Lyz\WeChat\Enums;

/**
 * 应用授权作用域
 * Class ScopeEnum
 * @package Lyz\WeChat\Enums
 */
class ScopeEnum
{
    /**
     * 不弹出授权页面，直接跳转，只能获取用户openid
     */
    const SNSAPI_BASE = 'snsapi_base';

    /**
     * 弹出授权页面，可通过openid拿到昵称、性别、所在地。
     * 并且，即使在未关注的情况下，只要用户授权，也能获取其信息 
     */
    const SNSAPI_USERINFO = 'snsapi_userinfo';
}
