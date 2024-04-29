<?php

namespace Lyz\WeMini\Doc;

use Lyz\Common\BaseObject;

/**
 * 用户手机号信息
 * Class PhoneNumberInfo
 * @package Lyz\WeMini\Doc
 */
class PhoneNumberInfo extends BaseObject
{
    /**
     * 用户绑定的手机号（国外手机号会有区号）
     *
     * @var string
     */
    public $phoneNumber;

    /**
     * 没有区号的手机号
     *
     * @var string
     */
    public $purePhoneNumber;

    /**
     * 区号
     *
     * @var string
     */
    public $countryCode;

    /**
     * 数据水印
     *
     * @var \Lyz\WeMini\Doc\Watermark
     */
    public $watermark;
}
