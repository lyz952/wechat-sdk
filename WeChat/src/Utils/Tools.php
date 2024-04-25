<?php

namespace Lyz\WeChat\Utils;

use Lyz\WeChat\Exceptions\InvalidResponseException;

/**
 * Class Tools
 * @package Lyz\WeChat\Utils
 */
class Tools
{
    /**
     * 解析 JSON 内容到数组
     * 
     * @param string $json
     * @return array
     * @throws \Lyz\WeChat\Exceptions\InvalidResponseException
     */
    public static function json2arr($json)
    {
        $result = json_decode($json, true);
        if (empty($result)) {
            throw new InvalidResponseException('invalid response.');
        }
        if (!empty($result['errcode'])) {
            throw new InvalidResponseException($result['errmsg'], $result['errcode'], $result);
        }
        return $result;
    }

    /**
     * 数组转JSON
     * 
     * @param array $data
     * @return string
     */
    public static function arr2json($data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $json === '[]' ? '{}' : $json;
    }

    /**
     * 解析XML内容到数组
     * 
     * @param string $xml
     * @return array
     */
    public static function xml2arr($xml)
    {
        if (PHP_VERSION_ID < 80000) {
            $backup = libxml_disable_entity_loader(true);
            $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            libxml_disable_entity_loader($backup);
        } else {
            $data = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return json_decode(json_encode($data), true);
    }

    /**
     * 数组转XML内容
     * 
     * @param array $data
     * @return string
     */
    public static function arr2xml($data)
    {
        return "<xml>" . self::_arr2xml($data) . "</xml>";
    }

    /**
     * XML内容生成
     * 
     * @param array $data 数据
     * @param string $content
     * @return string
     */
    private static function _arr2xml($data, $content = '')
    {
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = 'item';
            $content .= "<{$key}>";
            if (is_array($val) || is_object($val)) {
                $content .= self::_arr2xml($val);
            } elseif (is_string($val)) {
                $content .= '<![CDATA[' . preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/", '', $val) . ']]>';
            } else {
                $content .= $val;
            }
            $content .= "</{$key}>";
        }
        return $content;
    }

    /**
     * 检查回调函数是否有用
     * 
     * @param array $callback [类, 静态函数名]
     * @return boolean
     */
    public static function checkCallback($callback)
    {
        if (is_array($callback) && is_callable($callback, true, $callable_name)) return true;
        return false;
    }
}
