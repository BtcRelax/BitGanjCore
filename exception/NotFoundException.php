<?php

namespace BtcRelax\Exception;

class NotFoundException extends \Exception
{
    protected $message;

    public function __construct($message = null)
    {
        $this->message = \sprintf('While poccess URI:%s ,error was catched.%s', $_SERVER['REQUEST_URI'], $message !== null ? \sprintf('With message: %s', $message) : '');
        \BtcRelax\Log::general($this->message, \BtcRelax\Log::WARN);
    }
}
