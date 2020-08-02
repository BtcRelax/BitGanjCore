<?php

namespace BtcRelax\Exception;

class AssignBookmarkException extends \Exception {

    protected $message;

    public function __construct($message ) {
        $this->message = \sprintf('While poccess URI:%s cannot assign Bookmark:%s', $_SERVER['REQUEST_URI'], $message );
        \BtcRelax\Log::general( $this->message , \BtcRelax\Log::WARN);
    }

}
