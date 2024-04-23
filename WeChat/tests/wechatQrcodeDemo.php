<?php

include "../vendor/autoload.php";
include "./AccessTokenDemo.php";

use Lyz\WeChat\Qrcode;

try {
    // 配置参数
    $config = include "./config.php";
    $config = $config['wechat'];
    // 注册代替函数
    $config['GetAccessTokenCallback'] = [AccessTokenDemo::class, 'getAccessTokenCallback'];

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
