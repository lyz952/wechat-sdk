<?php

namespace Lyz\WeChat\Exceptions;

/**
 * 加密解密异常
 * Class InvalidDecryptException
 * @package Lyz\WeChat\Exceptions
 */
class InvalidDecryptException extends \Exception
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
