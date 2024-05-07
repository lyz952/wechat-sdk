<?php

namespace Lyz\WeChat\Utils;

use Lyz\WeChat\Exceptions\LocalCacheException;

/**
 * 缓存类
 * Class Cache
 * @package Lyz\Utils\Contracts
 */
class Cache
{
    /**
     * @var string 缓存路径
     */
    public $cache_path;

    /**
     * constructor.
     * 
     * @param string $cache_path 缓存路径
     * @return void
     */
    public function __construct($cache_path = null)
    {
        !is_null($cache_path) && $this->cache_path = $cache_path;
    }

    /**
     * 写入缓存
     * 
     * @param string $name    缓存名称
     * @param string $value   缓存内容
     * @param int    $expired 缓存时间(0表示永久缓存)
     * @return string 缓存路径
     */
    public function setCache($name, $value = '', $expired = 3600)
    {
        $file = $this->_getCacheName($name);
        $data = [
            'name' => $name,
            'value' => $value,
            'expired' => $expired === 0 ? 0 : time() + intval($expired)
        ];
        if (!file_put_contents($file, serialize($data))) {
            throw new LocalCacheException('local cache error.', '0');
        }
        return $file;
    }

    /**
     * 获取缓存
     * 
     * @param string $name 缓存名称
     * @return null|string
     */
    public function getCache($name)
    {
        $file = $this->_getCacheName($name);
        if (file_exists($file) && is_file($file) && ($content = file_get_contents($file))) {
            $data = unserialize($content);
            if (isset($data['expired']) && (intval($data['expired']) === 0 || intval($data['expired']) >= time())) {
                return $data['value'];
            }
            $this->delCache($name);
        }
        return null;
    }

    /**
     * 清除缓存
     * 
     * @param string $name 缓存名称
     * @return void
     */
    public function delCache($name)
    {
        $file = $this->_getCacheName($name);
        return !file_exists($file) || @unlink($file);
    }

    /**
     * 缓存目录
     * 
     * @param string $name
     * @return string
     */
    protected function _getCacheName($name)
    {
        if (empty($this->cache_path)) {
            $this->cache_path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR;
        }
        $this->cache_path = rtrim($this->cache_path, '/\\') . DIRECTORY_SEPARATOR;
        file_exists($this->cache_path) || mkdir($this->cache_path, 0755, true);
        return $this->cache_path . $name;
    }
}
