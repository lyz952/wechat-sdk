<?php

namespace Lyz\WeChat\Utils;

use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * Class Curl
 * @package Lyz\WeChat\Utils
 */
class Curl
{
    /**
     * 存储当前CURL请求的资源句柄
     */
    protected $ch;

    /**
     * @var string 唯一id
     */
    public $id;

    /**
     * @var string 请求地址
     */
    public $url;

    /**
     * @var array 请求头
     */
    private $headers = array();

    /**
     * 初始化 Curl 对象
     * 
     * @throws \ErrorException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        $this->id = uniqid('', true);
        $this->ch = curl_init();

        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->setOpt(CURLOPT_TIMEOUT, 60);
        $this->setOpt(CURLOPT_HEADER, false);
        $this->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOpt(CURLOPT_SSL_VERIFYHOST, false);
    }

    /**
     * 获取当前请求唯一id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 设置请求地址
     *
     * @param string $url
     * @return static
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 设置 CURLOPT 选项
     *
     * @param int   $option
     * @param mixed $value
     * @return boolean
     */
    public function setOpt($option, $value)
    {
        // 固定选项值
        $required_options = [
            CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER', // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
        ];

        if (in_array($option, array_keys($required_options), true) && $value !== true) {
            trigger_error($required_options[$option] . ' is a required option', E_USER_WARNING);
        }

        $success = curl_setopt($this->ch, $option, $value);

        return $success;
    }

    /**
     * 设置 CURLOPT 选项
     *
     * @param array $options
     * @return boolean
     */
    public function setOpts($options)
    {
        foreach ($options as $option => $value) {
            if (!$this->setOpt($option, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 设置请求头
     *
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
        $headers = [];
        foreach ($this->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }
        return $this->setOpt(CURLOPT_HTTPHEADER, $headers);
    }

    /**
     * GET
     * 
     * @param string $url
     * @return string
     **/
    public function get($url)
    {
        $this->setUrl($url);

        $this->setOpt(CURLOPT_HTTPGET, true);
        $this->setOpt(CURLOPT_CUSTOMREQUEST, 'GET');

        return $this->exec();
    }

    /**
     * POST
     *
     * @param string $url
     * @param array  $data 请求参数
     * @return string
     **/
    public function post($url, $data = [])
    {
        $this->setUrl($url);

        $this->setOpt(CURLOPT_POST, true);
        $this->setOpt(CURLOPT_POSTFIELDS, Tools::arr2json($data));

        return $this->exec();
    }

    /**
     * 执行
     *
     * @return string
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     **/
    public function exec()
    {
        if (empty($this->url)) {
            throw new InvalidResponseException('url is must');
        }

        $this->setOpt(CURLOPT_URL, $this->url);

        // 请求结果
        $response = curl_exec($this->ch);

        $error_code = curl_errno($this->ch);
        if ($error_code > 0) {
            $errorMessage = curl_strerror($error_code) . '(' . $error_code . '): ' . curl_error($this->ch);
            throw new InvalidResponseException($errorMessage);
        }

        $this->close();

        return $response;
    }

    /**
     * 关闭会话
     * 
     * @return void
     */
    public function close()
    {
        if (is_resource($this->ch) || $this->ch instanceof \CurlHandle) {
            curl_close($this->ch);
        }

        $this->ch = null;
    }
}
