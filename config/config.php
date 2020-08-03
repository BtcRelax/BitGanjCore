<?php

namespace BtcRelax;

use Exception;

/**
 * Application configuration.
 */
final class Config
{

    /** @var array config data */
    private static $DATA = null;


    /**
     * @return array
     * @throws Exception
     */
    private static function getConfig($section = null)
    {
        if ($section === null) {
            $section = gethostname();
        }
        $data = self::getData();
        if (!array_key_exists($section, $data)) {
            throw new Exception('Unknown config section: ' . $section);
        }
        return $data[$section];
    }
        
    public static function init()
    {
        $vConfig=self::getConfig("Global");
        if ($vConfig!==null) {
            foreach ($vConfig as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }
        

    /**
     * @return array
     */
    private static function getData()
    {
        if (self::$DATA !== null) {
            return self::$DATA;
        }
        self::$DATA = \parse_ini_file(__DIR__ . '/config.ini', true);
        return self::$DATA;
    }
}
