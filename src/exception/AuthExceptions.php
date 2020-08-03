<?php
namespace BtcRelax\Exception;

class AuthentificationCritical extends \Exception
{
    protected $message;
    
    public function __construct($message)
    {
        $this->message = \sprintf('Proccess URI:%s auth error, was throwed. %s', $_SERVER['REQUEST_URI'], $message);
        \BtcRelax\Log::general($this, \BtcRelax\Log::ERROR);
    }
}
