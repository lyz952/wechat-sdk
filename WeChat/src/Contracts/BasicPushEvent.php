<?php

namespace Lyz\WeChat\contracts;

use Lyz\WeChat\Utils\Tools;
use Lyz\WeChat\Aes\Prpcrypt;
use Lyz\WeChat\Exceptions\InvalidDecryptException;
use Lyz\WeChat\Exceptions\InvalidArgumentException;
use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * Class BasicPushEvent
 * 微信通知处理基本类
 * @package Lyz\WeChat\contracts
 */
class BasicPushEvent
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
     * @var string 令牌 - 公众号服务器配置
     */
    public $token;

    /**
     * @var string 消息加解密密钥 - 公众号服务器配置
     */
    public $encodingAESKey;

    /**
     * @var \Lyz\WeChat\contracts\DataArray 公众号的推送请求参数 $_REQUEST
     */
    protected $input;

    /**
     * @var string 公众号推送加密类型
     */
    protected $encryptType;

    /**
     * @var string 公众号推送XML内容
     */
    protected $postxml;

    /**
     * @var \Lyz\WeChat\contracts\DataArray 公众号推送内容对象
     */
    protected $receive;

    /**
     * @var array 准备回复的消息内容
     */
    protected $message;

    /**
     * constructor.
     * 
     * @param array $options
     * @throws InvalidResponseException
     */
    public function __construct(array $options)
    {
        if (empty($options['appId'])) {
            throw new InvalidArgumentException("Missing Config -- [appId]");
        }
        $this->appId = $options['appId'];
        if (empty($options['appSecret'])) {
            throw new InvalidArgumentException("Missing Config -- [appSecret]");
        }
        $this->appSecret = $options['appSecret'];
        if (empty($options['token'])) {
            throw new InvalidArgumentException("Missing Config -- [token]");
        }
        $this->token = $options['token'];

        // 参数初始化
        $this->input = new DataArray($_REQUEST);

        // 推送消息处理
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $this->postxml = file_get_contents("php://input");
            $this->encryptType = $this->input->get('encrypt_type');
            if ($this->isEncrypt()) {
                if (empty($options['encodingAESKey'])) {
                    throw new InvalidArgumentException("Missing Config -- [encodingAESKey]");
                }
                $this->encodingAESKey = $options['encodingAESKey'];

                $prpcrypt = new Prpcrypt($this->encodingAESKey);
                $result = Tools::xml2arr($this->postxml);
                $array = $prpcrypt->decrypt($result['Encrypt']);
                if (intval($array[0]) > 0) {
                    throw new InvalidResponseException($array[1], $array[0]);
                }
                list($this->postxml, $this->appId) = [$array[1], $array[2]];
            }
            $this->receive = new DataArray(Tools::xml2arr($this->postxml));
        } elseif ($_SERVER['REQUEST_METHOD'] == "GET" && $this->checkSignature()) {
            // 设置微信服务器配置时的相应验证，只会在设置时验证一次
            @ob_clean();
            exit($this->input->get('echostr'));
        } else {
            throw new InvalidResponseException('Invalid interface request.', '0');
        }
    }

    /**
     * 消息是否需要加密
     * 
     * @return boolean
     */
    public function isEncrypt()
    {
        return $this->encryptType === 'aes';
    }

    /**
     * 回复消息
     * 
     * @param array   $data 消息内容
     * @param boolean $return 是否返回XML内容
     * @param boolean $isEncrypt 是否加密内容
     * @return string
     * @throws \Lyz\WeChat\Exceptions\InvalidDecryptException
     */
    public function reply(array $data = [], $return = false, $isEncrypt = false)
    {
        $xml = Tools::arr2xml(empty($data) ? $this->message : $data);
        if ($this->isEncrypt() || $isEncrypt) {
            if (!class_exists('Prpcrypt', false)) {
                require __DIR__ . '/Prpcrypt.php';
            }
            $prpcrypt = new Prpcrypt($this->encodingAESKey);
            $array = $prpcrypt->encrypt($xml, $this->appId);
            if ($array[0] > 0) throw new InvalidDecryptException('Encrypt Error.', '0');
            list($timestamp, $encrypt) = [time(), $array[1]];
            $nonce = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $tmpArr = [$this->token, $timestamp, $nonce, $encrypt];
            sort($tmpArr, SORT_STRING);
            $signature = sha1(implode($tmpArr));
            $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
            $xml = sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }
        if ($return) return $xml;
        @ob_clean();
        echo $xml;
    }

    /**
     * 获取公众号推送对象
     * 
     * @param null|string $field 指定获取字段
     * @return string|array
     */
    public function getReceive($field = null)
    {
        return $this->receive->get($field);
    }

    /**
     * 获取当前推送消息类型
     * 
     * @return string
     */
    public function getMsgType()
    {
        return $this->receive->get('MsgType');
    }

    /**
     * 获取当前推送消息ID
     * 
     * @return string
     */
    public function getMsgId()
    {
        return $this->receive->get('MsgId');
    }

    /**
     * 获取消息创建时间
     * 
     * @return integer
     */
    public function getMsgCreateTime()
    {
        return $this->receive->get('CreateTime');
    }

    /**
     * 获取发送方账号
     * 
     * @return string
     */
    public function getOpenid()
    {
        return $this->receive->get('FromUserName');
    }

    /**
     * 获取当前推送公众号
     * 
     * @return string
     */
    public function getToOpenid()
    {
        return $this->receive->get('ToUserName');
    }

    /**
     * 验证来自微信服务器，设置微信服务器配置时的验证，只会在设置时验证一次
     * 
     * @param string $str
     * @return bool
     */
    private function checkSignature($str = '')
    {
        $nonce = $this->input->get('nonce');
        $timestamp = $this->input->get('timestamp');
        $msg_signature = $this->input->get('msg_signature');
        $signature = empty($msg_signature) ? $this->input->get('signature') : $msg_signature;

        $tmpArr = [$this->token, $timestamp, $nonce, $str];
        sort($tmpArr, SORT_STRING);
        return sha1(implode($tmpArr)) === $signature;
    }
}
