<?php

namespace BtcRelax\Exception;

class SessionException extends \Exception {

    protected $message;

    public function __construct($message = null) {
        $this->message = \sprintf('Session access error while poccess URI:%s was catched.%s', $_SERVER['REQUEST_URI'], $message !== null ? \sprintf('With message: %s', $message) : '' );
        \BtcRelax\Log::general( \sprintf('%s\n Stack trace:%s',$this->message, $this->getTraceAsString()) , \BtcRelax\Log::FATAL);
    }

}
