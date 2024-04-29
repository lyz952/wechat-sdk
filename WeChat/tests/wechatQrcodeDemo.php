<?php

include "../vendor/autoload.php";
include "./AccessTokenDemo.php";

use Lyz\WeChat\Qrcode;
use Lyz\WeChat\Contracts\accessTokenCache;

try {
    // 配置参数
    $config = include "./config.php";
    $config = $config['wechat'];
    $config['accessTokenCache'] = new accessTokenCache();

    $qrcode = new Qrcode($config);
    // 获取 ticket
    $ticketInfo = $qrcode->create('test');
    var_dump($ticketInfo);

    // 创建二维码链接
    $url = $qrcode->url($ticketInfo->ticket);
    var_dump($url);
} catch (Exception $e) {
    // 出错啦，处理下吧
    echo $e->getMessage() . PHP_EOL;
}
