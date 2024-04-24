<?php

namespace Lyz\WeChat;

use Lyz\WeChat\contracts\BasicWeChat;
use Lyz\WeChat\Doc\CreateQrcodeResponse;

/**
 * 二维码管理
 * Class Qrcode
 * doc: https://developers.weixin.qq.com/doc/offiaccount/Account_Management/Generating_a_Parametric_QR_Code.html
 * @package Lyz\WeChat
 */
class Qrcode extends BasicWeChat
{
    /**
     * 创建二维码
     * 获取带参数的二维码的过程包括两步，首先获取二维码ticket，然后凭借ticket到指定URL换取二维码
     *
     * @param string|int $scene          二维码场景值 
     *  int: 临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     *  string: 长度限制为1到64
     * @param int        $expire_seconds 二维码有效时间，以秒为单位，最大2592000秒(30天)，为0则是永久二维码
     * @return \Lyz\WeChat\Doc\CreateQrcodeResponse
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function create($scene, $expire_seconds = 60)
    {
        /*
            临时二维码: 是有过期时间的，最长可以设置为在二维码生成后的30天（即2592000秒）后过期，但能够生成较多数量。
            永久二维码: 是无过期时间的，但数量较少（目前为最多10万个）。
        */
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN";
        $this->registerApi($url);

        // 二维码场景类型
        if (is_integer($scene)) {
            $data = ['action_info' => ['scene' => ['scene_id' => $scene]]];
        } else {
            $data = ['action_info' => ['scene' => ['scene_str' => $scene]]];
        }
        if ($expire_seconds > 0) {
            // 临时二维码
            $data['expire_seconds'] = $expire_seconds;
            $data['action_name'] = is_integer($scene) ? 'QR_SCENE' : 'QR_STR_SCENE';
        } else {
            // 永久二维码
            $data['action_name'] = is_integer($scene) ? 'QR_LIMIT_SCENE' : 'QR_LIMIT_STR_SCENE';
        }

        return CreateQrcodeResponse::create($this->callPostApi($url, $data));
    }

    /**
     * 通过ticket换取二维码
     * 
     * @param string $ticket 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     * @return string 直接echo本函数的返回值，并在调用页面添加header('Content-type: image/jpg');，将会展示出一个二维码的图片。
     */
    public function url($ticket)
    {
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
    }
}
