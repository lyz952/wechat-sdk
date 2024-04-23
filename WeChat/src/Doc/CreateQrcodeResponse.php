<?php

namespace Lyz\WeChat\Doc;

use Lyz\Common\BaseObject;

/**
 * 创建二维码应答参数
 * Class CreateQrcodeResponse
 * @package Lyz\WeChat\Doc
 */
class CreateQrcodeResponse extends BaseObject
{
    /**
     * 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     *
     * @var string
     */
    public $ticket;

    /**
     * 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天）。
     *
     * @var int
     */
    public $expire_seconds;

    /**
     * 二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片
     *
     * @var string
     */
    public $url;
}
