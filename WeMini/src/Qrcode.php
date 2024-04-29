<?php

namespace Lyz\WeMini;

use Lyz\WeChat\Utils\Curl;
use Lyz\WeChat\Utils\Tools;
use Lyz\WeChat\Contracts\BasicWeChat;
use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * 微信小程序二维码
 * Class Qrcode
 * @package Lyz\WeMini
 */
class Qrcode extends BasicWeChat
{
    /**
     * 默认线条颜色
     * 
     * @var string[]
     */
    private $lineColor = ["r" => "0", "g" => "0", "b" => "0"];

    /**
     * 获取不限制的小程序码（永久有效）适用于需要的码数量极多的业务场景
     * 
     * @param string $scene     最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
     * @param string $page      必须是已经发布的小程序存在的页面，默认是主页，例如 pages/index/index，根路径前不要填加 /，不能携带参数（参数请放在scene字段里）。scancode_time为系统保留参数，不允许配置
     * @param int    $width     二维码的宽度，单位 px，最小 280px，最大 1280px
     * @param bool   $autoColor 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param array  $lineColor 使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示，auto_color 为 false 时生效
     * @param bool   $isHyaline 是否需要透明底色
     * @return string 图片 Buffer
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function createMiniScene($scene, $page = '', $width = 430, $autoColor = false, $lineColor = null, $isHyaline = true)
    {
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());

        $lineColor = empty($lineColor) ? $this->lineColor : $lineColor;
        $data = [
            'scene' => $scene,
            'page' => $page,
            'check_path' => false, // 检查page 是否存在，为 true 时 page 必须是已经发布的小程序存在的页面（否则报错）；为 false 时允许小程序未发布或者 page 不存在， 但page 有数量上限（60000个）请勿滥用。
            'env_version' => 'release', // 要打开的小程序版本。正式版为 "release"，体验版为 "trial"，开发版为 "develop"。
            'width' => $width,
            'auto_color' => $autoColor,
            'line_color' => $lineColor,
            'is_hyaline' => $isHyaline,
        ];
        if (empty($page)) unset($data['page']);

        return $this->parseResult((new Curl())->post($url, Tools::arr2json($data)));
    }

    /**
     * 解释接口数据
     * 
     * @param string $result
     * @return array|mixed
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    private function parseResult($result)
    {
        if (is_array($json = json_decode($result, true))) {
            if (isset($json['errcode']) && in_array($json['errcode'], [
                '41001', '42001',
                // '40014', '40001',
            ])) {
                if (!empty($this->accessTokenCache)) $this->accessTokenCache::clearToken();
                return call_user_func_array([$this, $this->currentMethod['method']], $this->currentMethod['arguments']);
            }
            return Tools::json2arr($result);
        } else {
            return $result;
        }
    }
}
