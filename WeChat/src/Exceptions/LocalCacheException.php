<?php

namespace Lyz\WeChat\Exceptions;

/**
 * 本地缓存异常
 * Class LocalCacheException
 * @package Lyz\WeChat\Exceptions
 */
class LocalCacheException extends \Exception
{
    /**
     * @var array
     */
    public $raw = [];

    /**
     * constructor.
     * 
     * @param string  $message
     * @param integer $code
     * @param array   $raw
     */
    public function __construct($message, $code = 0, $raw = [])
    {
        parent::__construct($message, $code);
        $this->raw = $raw;
    }
}
