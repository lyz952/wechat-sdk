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

    // /**
    //  * @var array 与请求一起发送的 CURLOPT 选项的关联数组
    //  */
    // protected $options = array();

    /**
     * 初始化 Curl 对象
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
        if ($success) {
            // $this->options[$option] = $value;
        }
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

    // /**
    //  * 获取已经设置的 CURLOPT 选项的值
    //  *
    //  * @param int $option
    //  * @return mixed
    //  */
    // public function getOpt($option)
    // {
    //     return isset($this->options[$option]) ? $this->options[$option] : null;
    // }

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
        $this->setOpt(CURLOPT_POSTFIELDS, self::_buildPostData($data));

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

        // CURLOPT_HEADER 为 true 时使用下面方法提取数据
        // // headers 正则表达式
        // $pattern = '#HTTP/\d\.\d.*?$.*?\r\n\r\n#ims';

        // // 从响应中提取 headers
        // preg_match_all($pattern, $response, $matches);
        // $headers_string = array_pop($matches[0]);
        // $headers = explode("\r\n", str_replace("\r\n\r\n", '', $headers_string));

        // // 从响应正文中删除 headers
        // $body = str_replace($headers_string, '', $response);

        // // 从第一个 headers 中提取版本和状态
        // $version_and_status = array_shift($headers);
        // preg_match('#HTTP/(\d\.\d)\s(\d\d\d)\s(.*)#', $version_and_status, $matches);
        // $headers['Http-Version'] = $matches[1];
        // $headers['Status-Code'] = $matches[2];
        // $headers['Status'] = $matches[2] . ' ' . $matches[3];

        // // headers 转换为关联数组
        // foreach ($headers as $header) {
        //     preg_match('#(.*?)\:\s(.*)#', $header, $matches);
        //     $headers[$matches[1]] = $matches[2];
        // }

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
        // $this->options = null;
    }

    /**
     * POST 数据过滤处理
     * 
     * @param array $data
     * @return string
     */
    private static function _buildPostData($data)
    {
        if (!is_array($data)) return $data;

        return Tools::arr2json($data);
    }

    // /**
    //  * Set Headers
    //  *
    //  * @param string[] $headers
    //  */
    // public function setHeaders($headers)
    // {
    //     foreach ($headers as $header) {
    //         list($key, $value) = explode(':', $header, 2);
    //         $key = trim($key);
    //         $value = trim($value);
    //         $this->headers[$key] = $value;
    //     }

    //     $headers = [];
    //     foreach ($this->headers as $key => $value) {
    //         $headers[] = $key . ': ' . $value;
    //     }

    //     $this->setOpt(CURLOPT_HTTPHEADER, $headers);
    // }

    // /**
    //  * 从请求中删除内部标头
    //  * Using `curl -H "Host:" ...' is equivalent to $ch->removeHeader('Host');.
    //  *
    //  * @access public
    //  * @param  $key
    //  */
    // public function removeHeader($key)
    // {
    //     $this->setHeader($key, '');
    // }

    // /**
    //  * 设置证书文件
    //  *
    //  * @param [type] $ssl_key
    //  * @return void
    //  */
    // public function setSslKey($ssl_key)
    // {
    //     if (!file_exists($ssl_key)) {
    //         throw new InvalidArgumentException("Certificate files that do not exist. --- [ssl_key]");
    //     }

    //     curl_setopt($this->ch, CURLOPT_SSLKEYTYPE, 'PEM');
    //     curl_setopt($this->ch, CURLOPT_SSLKEY, $ssl_key);

    //     return $this;
    // }

    // /**
    //  * 设置证书文件
    //  *
    //  * @param [type] $ssl_cer
    //  * @return void
    //  */
    // public function setSslCer($ssl_cer)
    // {
    //     if (!file_exists($ssl_cer)) {
    //         throw new InvalidArgumentException("Certificate files that do not exist. --- [ssl_cer]");
    //     }

    //     curl_setopt($this->ch, CURLOPT_SSLCERTTYPE, 'PEM');
    //     curl_setopt($this->ch, CURLOPT_SSLCERT, $ssl_cer);

    //     return $this;
    // }
}
