<?php

namespace BtcRelax;

class Logger {

    const FATAL = -1;
    const ERROR = 0;
    const WARN = 1;
    const INFO = 2;
    const DEBUG = 3;

    public static function general($msg, $logLevel = 0) {
         $date = date('d.m.Y h:i:s');
            $log = \sprintf('%s: Level: %s|', $date, $logLevel);
            if (!empty(session_id())) {
                $log .= \sprintf('Session: %s|', session_id());
            }
            if (($msg instanceof \Error) || ($msg instanceof \Exception)) {
                $log .= \sprintf("Message: %s \n Trace: \n %s", $msg->getMessage(), $msg->getTraceAsString());
            } else {
                $log .= $msg;
            }
            \error_log($log);
            if ($logLevel < self::ERROR) {
                die;
            }
    }

}
