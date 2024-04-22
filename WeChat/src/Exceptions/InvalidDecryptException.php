<?php

namespace Lyz\WeChat\Exceptions;

/**
 * Class InvalidDecryptException
 * 加密解密异常
 * @package Lyz\WeChat\Exceptions
 */
class InvalidDecryptException extends \Exception
{
    /**
     * @var array
     */
    public $raw = [];

    /**
     * InvalidDecryptException constructor.
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
