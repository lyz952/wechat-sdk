<?php

namespace Lyz\WeChat\Exceptions;

/**
 * 接口参数异常
 * Class InvalidArgumentException
 * @package Lyz\WeChat\Exceptions
 */
class InvalidArgumentException extends \InvalidArgumentException
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
