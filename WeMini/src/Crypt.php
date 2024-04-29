<?php

namespace Lyz\WeMini;

use Lyz\WeChat\Contracts\BasicWeChat;
use Lyz\WeChat\Exceptions\ErrorMsg;
use Lyz\WeMini\Doc\PhoneNumberInfo;
use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * 数据加密处理
 * Class Crypt
 * @package Lyz\WeMini
 */
class Crypt extends BasicWeChat
{
    /**
     * 通过授权码换取手机号
     * 
     * @param string $code 每个code只能使用一次，code的有效期为5min
     * @return array
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
