<?php
namespace BtcRelax\Exception;

class AccessDeniedException extends \Exception
{
    protected $message;
    
    public function __construct($message)
    {
        $this->message = \sprintf('Access denied while proccess URI:%s. Message:%s', $_SERVER['REQUEST_URI'], $message);
        \BtcRelax\Log::general($this, \BtcRelax\Log::ERROR);
    }
}
