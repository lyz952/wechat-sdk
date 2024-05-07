<?php

namespace Lyz\WeChat;

use Lyz\WeChat\Contracts\BasicWeChat;
use Lyz\WeChat\Doc\JsSignResponse;
use Lyz\WeChat\Exceptions\ErrorMsg;
use Lyz\WeChat\Exceptions\InvalidResponseException;
use Lyz\WeChat\Utils\Tools;

/**
 * 微信前端支持
 * Class Script
 * @package Lyz\WeChat
 */
class Script extends BasicWeChat
{
    /**
     * 获取 JSAPI_TICKET
     * 
     * @param string $type  TICKET类型(wx_card|jsapi)
     * @param string $appid 强制指定有效APPID
     * @return string jsapi_ticket是公众号用于调用微信JS接口的临时票据，有效期为7200秒
     * @throws \Lyz\WeChat\Exceptions\LocalCacheException
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function getTicket($type = 'jsapi', $appid = null)
    {
        is_null($appid) && $appid = $this->appId;
        $cacheName = "{$appid}_ticket_{$type}";

        if (!empty($this->cacheTool)) {
            $ticket = $this->cacheTool->getCache($cacheName);
            if (!empty($ticket)) {
                return $ticket;
            }
        }

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=ACCESS_TOKEN&type={$type}";
        $this->registerApi($url, __FUNCTION__, func_get_args());

        $result = $this->callGetApi($url);
        if (empty($result['ticket'])) {
            throw new InvalidResponseException('Invalid Resoponse Ticket.', ErrorMsg::ERROR_SYSTEM);
        }

        if (!empty($this->cacheTool)) {
            $this->cacheTool->setCache($cacheName, $result['ticket'], 7000);
        }

        return $result['ticket'];
    }

    /**
     * 删除 JSAPI 授权 TICKET
     * @param string $type  TICKET类型(wx_card|jsapi)
     * @param string $appid 强制指定有效APPID
     * @return void
     */
    public function delTicket($type = 'jsapi', $appid = null)
    {
        is_null($appid) && $appid = $this->appId;
        $cacheName = "{$appid}_ticket_{$type}";
        if (!empty($this->cacheTool)) {
            $this->cacheTool->delCache($cacheName);
        }
    }

    /**
     * 获取 JsApi 使用签名
     * 
     * @param string $url       网页的URL
     * @param string $appid     用于多个appid时使用(可空)
     * @param string $ticket    强制指定ticket
     * @param array  $jsApiList 需初始化的 jsApiList
     * @return \Lyz\WeChat\Doc\JsSignResponse
     * @throws \Lyz\WeChat\Exceptions\LocalCacheException
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function getJsSign($url, $appid = null, $ticket = null, $jsApiList = null)
    {
        list($url,) = explode('#', $url);
        is_null($ticket) && $ticket = $this->getTicket('jsapi');
        is_null($appid) && $appid = $this->appId;
        is_null($jsApiList) && $jsApiList = [
            'updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
            'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
            'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
            'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
            'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
        ];
        $data = ["url" => $url, "timestamp" => '' . time(), "jsapi_ticket" => $ticket, "noncestr" => Tools::createNoncestr(16)];

        $jsSignResponse = new JsSignResponse();
        $jsSignResponse->appId = $appid;
        $jsSignResponse->nonceStr = $data['noncestr'];
        $jsSignResponse->timestamp = $data['timestamp'];
        $jsSignResponse->signature = $this->getSignature($data, 'sha1');
        $jsSignResponse->jsApiList = $jsApiList;
        // $jsSignResponse->debug = false;

        return $jsSignResponse;
    }

    /**
     * 数据生成签名
     * 
     * @param array  $data 签名数组
     * @param string $method 签名方法
     * @param array  $params 签名参数
     * @return bool|string 签名值
     */
    protected function getSignature($data, $method = "sha1", $params = [])
    {
        ksort($data);
        if (!function_exists($method)) return false;
        foreach ($data as $k => $v) $params[] = "{$k}={$v}";
        return $method(join('&', $params));
    }
}
