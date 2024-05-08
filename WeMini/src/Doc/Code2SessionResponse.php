<?php

namespace Lyz\WeMini\Doc;

use Lyz\Common\BaseObject;

/**
 * 小程序登录
 * Class Code2SessionResponse
 * @package Lyz\WeMini\Doc
 */
class Code2SessionResponse extends BaseObject
{
    /**
     * 用户唯一标识
     *
     * @var string
     */
    public $openid;

    /**
     * 会话密钥
     * 
     * @var string
     */
    public $session_key;

    /**
     * 用户在开放平台的唯一标识符，若当前小程序已绑定到微信开放平台账号下会返回
     *
     * @var string
     */
    public $unionid;
}
