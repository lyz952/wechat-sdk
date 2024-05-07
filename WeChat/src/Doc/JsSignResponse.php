<?php

namespace Lyz\WeChat\Doc;

use Lyz\Common\BaseObject;

/**
 * JS-SDK使用权限签名
 * Class JsSignResponse
 * @package Lyz\WeChat\Doc
 */
class JsSignResponse extends BaseObject
{
    /**
     * appId
     *
     * @var string
     */
    public $appId;

    /**
     * 随机字符串
     *
     * @var string
     */
    public $nonceStr;

    /**
     * 时间戳
     *
     * @var string
     */
    public $timestamp;

    /**
     * 签名
     *
     * @var string
     */
    public $signature;

    /**
     * JS接口列表
     *
     * @var array
     */
    public $jsApiList;
}
