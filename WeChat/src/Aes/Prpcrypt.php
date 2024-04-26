<?php

namespace Lyz\WeChat\Aes;

use Lyz\WeChat\Exceptions\InvalidDecryptException;

/**
 * Class Prpcrypt
 * 公众号消息 - 加解密
 * @package Lyz\WeChat\Aes
 */
class Prpcrypt
{
    public $key;

    /**
     * constructor.
     * 
     * @param string $key
     */
    function __construct($key)
    {
        $this->key = base64_decode($key . "=");
    }

    /**
     * 对明文进行加密
     * 
     * @param string $text  需要加密的明文
     * @param string $appid 公众号APPID
     * @return string
     * @throws \Lyz\WeChat\Exceptions\InvalidDecryptException
     */
    public function encrypt($text, $appid)
    {
        try {
            // 获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();
            // 使用自定义的填充方式对明文进行补位填充
            $text = $random . pack("N", strlen($text)) . $text . $appid;
            $pkcEncoder = new PKCS7Encoder();
            $text = $pkcEncoder->encode($text);

            $iv = substr($this->key, 0, 16);

            return openssl_encrypt($text, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        } catch (\Exception $e) {
            throw new InvalidDecryptException(ErrorCode::getErrText(ErrorCode::$EncryptAESError), ErrorCode::$EncryptAESError);
        }
    }

    /**
     * 对密文进行解密
     * 
     * @param string $encrypted 需要解密的密文
     * @return array 解密得到的明文
     * @throws \Lyz\WeChat\Exceptions\InvalidDecryptException
     */
    public function decrypt($encrypted)
    {
        try {
            $iv = substr($this->key, 0, 16);
            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', substr($this->key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        } catch (\Exception $e) {
            throw new InvalidDecryptException(ErrorCode::getErrText(ErrorCode::$DecryptAESError), ErrorCode::$DecryptAESError);
        }

        try {
            $pkcEncoder = new PKCS7Encoder();
            $result = $pkcEncoder->decode($decrypted);
            if (strlen($result) < 16) {
                throw new InvalidDecryptException(ErrorCode::getErrText(ErrorCode::$DecryptAESError), ErrorCode::$DecryptAESError);
            }
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            return [substr($content, 4, $xml_len), substr($content, $xml_len + 4)];
        } catch (\Lyz\WeChat\Exceptions\InvalidDecryptException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new InvalidDecryptException(ErrorCode::getErrText(ErrorCode::$IllegalBuffer), ErrorCode::$IllegalBuffer);
        }
    }

    /**
     * 随机生成16位字符串
     * 
     * @param string $str
     * @return string 生成的字符串
     */
    private function getRandomStr($str = "")
    {
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}
