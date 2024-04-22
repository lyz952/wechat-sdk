<?php

namespace Lyz\WeChat;

use Lyz\WeChat\contracts\BasicWeChat;

/**
 * Class Qrcode
 * 二维码管理
 * @package Lyz\WeChat
 */
class Qrcode extends BasicWeChat
{
    /**
     * 创建二维码
     * 获取带参数的二维码的过程包括两步，首先获取二维码ticket，然后凭借ticket到指定URL换取二维码
     *
     * @param string|int $scene          二维码场景值
     * @param int        $expire_seconds 二维码有效时间 默认600秒 最大2592000秒(30天)
     * @return array [
     *                   "ticket" => "gQE88DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyUDA2UHdpRWVlU0MxZktrR2h6MWwAAgSW0ipjAwRYAgBB",
     *                   "expire_seconds" => 600,
     *                   "url" => "http://weixin.qq.com/q/02P06PwiEeeSC1fKkGhz1l"
     *               ]
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function create($scene, $expire_seconds = 600)
    {
        /*
            GET https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN
                |参数	      |类型   |是否必须 |描述
            请求:
                |expire_seconds |int    |是 |该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为60秒。
                |action_name	|string |是 |二维码类型，QR_SCENE为临时的整型参数值，QR_STR_SCENE为临时的字符串参数值，QR_LIMIT_SCENE为永久的整型参数值，QR_LIMIT_STR_SCENE为永久的字符串参数值
                |action_info	|map    |是 |二维码详细信息
                |---scene_id    |int    |是 |场景值，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
                |---scene_str   |string |是 |场景值，字符串类型，长度限制为1到64
        
            返回:
                |ticket         |string |是 |获取的二维码ticket，凭借此 ticket 可以在有效时间内换取二维码。
                |expire_seconds	|int	|是 |该二维码有效时间，以秒为单位。最大不超过2592000（即30天）。
                |url	        |int	|是 |二维码图片解析后的地址，开发者可根据该地址自行生成需要的二维码图片。

            1、临时二维码，是有过期时间的，最长可以设置为在二维码生成后的30天（即2592000秒）后过期，但能够生成较多数量。 临时二维码主要用于账号绑定等不要求二维码永久保存的业务场景 
            2、永久二维码，是无过期时间的，但数量较少（目前为最多10万个）。永久二维码主要用于适用于账号绑定、用户来源统计等场景。
        */
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=ACCESS_TOKEN";

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
        return $this->callPostApi($url, $data);
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

    /**
     * 长链接转短链接
     * 主要使用场景：
     *  开发者用于生成二维码的原链接（商品、支付二维码等）太长导致扫码速度和成功率下降，
     *  将原长链接通过此接口转成短链接再生成二维码将大大提升扫码速度和成功率。
     * 
     * @param string $longUrl 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return array ['short_url'=>'http://t.cn/asdasd']
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function shortUrl($longUrl)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/shorturl?access_token=ACCESS_TOKEN";
        return $this->callPostApi($url, ['action' => 'long2short', 'long_url' => $longUrl]);
    }
}
