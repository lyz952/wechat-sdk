<?php

namespace Lyz\WeChat\contracts;

use Lyz\WeChat\Utils\Curl;
use Lyz\WeChat\Utils\Tools;
use Lyz\WeChat\Exceptions\ErrorMsg;
use Lyz\WeChat\Exceptions\InvalidArgumentException;
use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * Class BasicWeChat
 * @package Lyz\WeChat\contracts
 */
class BasicWeChat
{
    /**
     * @var string 公众号开发者ID
     */
    public $appId;

    /**
     * @var string 公众号开发者密码
     */
    public $appSecret;

    /**
     * @var static 静态缓存
     */
    protected static $instances;

    /**
     * @var array 注册代替函数 例: [类, 函数名] 如类不是实例化，则函数必须是静态函数
     */
    protected $GetAccessTokenCallback;

    /**
     * 构造函数
     * 
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (empty($options['appId'])) {
            throw new InvalidArgumentException("Missing Config -- [appId]");
        }
        $this->appId = $options['appid'];

        if (empty($options['appSecret'])) {
            throw new InvalidArgumentException("Missing Config -- [appSecret]");
        }
        $this->appSecret = $options['appSecret'];

        if (isset($options['GetAccessTokenCallback']) && Tools::checkCallback($options['GetAccessTokenCallback'])) {
            $this->GetAccessTokenCallback = $options['GetAccessTokenCallback'];
        }
    }

    /**
     * 静态创建对象
     * 
     * @param array $config
     * @return static
     */
    public static function instance(array $config)
    {
        $key = md5(get_called_class() . serialize($config));
        if (isset(self::$instances[$key])) return self::$instances[$key];
        return self::$instances[$key] = new static($config);
    }

    /**
     * 获取 AccessToken
     *
     * @return string 新凭证有效时间 7200秒
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function getAccessToken()
    {
        if (!empty($this->GetAccessTokenCallback) && Tools::checkCallback($this->GetAccessTokenCallback)) {
            return call_user_func_array($this->GetAccessTokenCallback, [$this]);
        }

        /*
            GET https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET
                |参数	      |类型   |是否必须 |描述
            请求:
                |grant_type	  |string |是 |获取 access_token 填写 client_credential
                |appid	      |string |是 |第三方用户唯一凭证
                |secret	      |string |是 |第三方用户唯一凭证密钥，即appsecret
        
            返回:
                |access_token |string |是 |获取到的凭证
                |expires_in	  |int	  |是 |凭证有效时间。7200秒

            错误:  
                |errcode      |string |是 |错误码 例: "40013",
                |errmsg       |string |是 |错误信息 例: "invalid appid"

            注：access_token 需要统一存储，避免冲突
            使用的时候，判断是否过期，如果过期就重新调用此方法获取，存取操作请自行完成
        */
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
        $curl = new Curl();
        $result = Tools::json2arr($curl->get($url));
        if (!isset($result['access_token'])) {
            throw new InvalidResponseException(ErrorMsg::toMessage(ErrorMsg::ERROR_GET_ACCESS_TOKEN), ErrorMsg::ERROR_GET_ACCESS_TOKEN, $result);
        }

        return $result['access_token'];
    }

    /**
     * 注册当前请求接口
     * 
     * @param string $url 接口地址
     * @return string
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    protected function registerApi(&$url)
    {
        $access_token = $this->getAccessToken();
        return $url = str_replace('ACCESS_TOKEN', urlencode($access_token), $url);
    }

    /**
     * GET请求
     * 
     * @param string $url 接口地址
     * @return array
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function callGetApi($url)
    {
        $this->registerApi($url);

        $curl = new Curl();
        return Tools::json2arr($curl->get($url));
    }

    /**
     * POST请求
     * 
     * @param string $url  接口地址
     * @param array  $data 请求参数
     * @return array
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public function callPostApi($url, $data)
    {
        $this->registerApi($url);

        $curl = new Curl();
        $curl->setHeader('Content-Type', 'application/json');
        return Tools::json2arr($curl->post($url, Tools::arr2json($data)));
    }
}
