<?php

namespace Lyz\WeMini;

use Lyz\WeChat\Contracts\BasicWeChat;
use Lyz\WeChat\Exceptions\ErrorMsg;
use Lyz\WeMini\Doc\PhoneNumberInfo;
use Lyz\WeMini\Doc\Code2SessionResponse;
use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * 数据加密处理
 * Class Crypt
 * @package Lyz\WeMini
 */
class Crypt extends BasicWeChat
{
    /**
     * 登录凭证校验
     * doc: https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/code2Session.html
     * @param string $code 登录时获取的 code，通过wx.login获取
     * @return \Lyz\WeMini\Doc\Code2SessionResponse
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function session($code)
    {
        $appid = $this->appId;
        $secret = $this->appSecret;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        return Code2SessionResponse::create($this->callGetApi($url));
    }

    /**
     * 通过授权码换取手机号
     * 
     * @param string $code 每个code只能使用一次，code的有效期为5min
     *  code获取: https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/getPhoneNumber.html
     * @return \Lyz\WeMini\Doc\PhoneNumberInfo
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function getPhoneNumber($code)
    {
        $url = 'https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=ACCESS_TOKEN';
        $this->registerApi($url, __FUNCTION__, func_get_args());

        $result = $this->callPostApi($url, ['code' => $code]);
        if (!isset($result['phone_info'])) {
            throw new InvalidResponseException('信息获取失败', ErrorMsg::ERROR_SYSTEM, $result);
        }

        return PhoneNumberInfo::create($result['phone_info']);
    }
}
