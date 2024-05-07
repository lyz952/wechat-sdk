<?php

namespace Lyz\WeMini\Doc;

use Lyz\Common\BaseObject;

/**
 * 水印
 * Class Watermark
 * @package Lyz\WeMini\Doc
 */
class Watermark extends BaseObject
{
    /**
     * 用户获取手机号操作的时间戳
     *
     * @var int
     */
    public $timestamp;

    /**
     * 小程序 appid
     *
     * @var string
     */
    public $appid;
}
