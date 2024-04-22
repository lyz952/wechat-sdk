<?php

namespace Lyz\WeChat\Exceptions;

/**
 * Class InvalidResponseException
 * 返回异常
 * @package Lyz\WeChat\Exceptions
 */
class InvalidResponseException extends \Exception
{
    /**
     * @var array
     */
    public $raw = [];

    /**
     * InvalidResponseException constructor.
     * @param string $message
     * @param integer $code
     * @param array $raw
     */
    public function __construct($message, $code = 0, $raw = [])
    {
        parent::__construct($message, $code);
        $this->raw = $raw;
    }
}
