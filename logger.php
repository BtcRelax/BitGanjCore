<?php
namespace BtcRelax;

class Log
{

    //
    const USER_ERROR_DIR = '/logs/user-errors.log';
    const GENERAL_ERROR_DIR = '/logs/general_errors.log';
    const FATAL = -1;
    const ERROR = 0;
    const WARN = 1;
    const INFO = 2;
    const DEBUG = 3;

    public static function user($msg, $username, $logLevel = 0)
    {
        $max = self::getMaxLogLevel();
        if ($logLevel >= $max) {
            $date = date('d.m.Y h:i:s');
            $log = $msg . " |  Date:  " . $date . " |  User:  " . $username . "\n";
            error_log($log, 3, ABS_PATH . self::USER_ERROR_DIR);
        }
    }

    public static function general($msg, $logLevel = 0)
    {
        $max = self::getMaxLogLevel();
        if ($logLevel <= $max) {
            $date = date('d.m.Y h:i:s');
            $log =  \sprintf('Level: %s|', $logLevel);
            if (!empty(session_id())) {
                $log .= \sprintf('Session: %s|', session_id());
            }
            if ($msg instanceof \Error) {
                $log .= \sprintf("Message: %s", $msg->getMessage()) . "\n" . \sprintf("Trace: \n %s", $msg->getTraceAsString());
            } elseif ($msg instanceof \Exception) {
                $log .= \sprintf("Message: %s", $msg->getMessage()) . "\n" . \sprintf("Trace: \n %s ", $msg->getTraceAsString());
            } else {
                $log .= $msg;
            }
            if (!defined('ABS_PATH')) {
                error_log(\sprintf('%s: %s ', $date, $log). "\n", 0);
            } else {
                $vPath = ABS_PATH . self::GENERAL_ERROR_DIR;
                \error_log(\sprintf('%s: %s', $date, $log). "\n", 3, $vPath);
            }
            if ($logLevel < 0) {
                die;
            }
        }
    }

    public static function getMaxLogLevel()
    {
        if (!defined('LOG_LEVEL')) {
            require_once __DIR__ . '/config/config.php';
            Config::init();
        }
        return LOG_LEVEL;
    }
}
