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
        if (empty($section)) { 
                return self::getData();
            }
        if (!\array_key_exists($section, self::$DATA)) {
                throw new \Exception (\sprintf('Unknown config section: %s', $section));
            }
        return self::$DATA[$section];
    }
        
    public static function init()
    {
        $cfgfile = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT') . '/config/config.ini';
        if (!file_exists($cfgfile)) {
            throw new \Exception(\sprintf('Config file:%s does not exists!',$cfgfile));
        } else {
            self::$DATA = \parse_ini_file($cfgfile, true);
        }
    }
        

    /**
     * @return array
     */
    private static function getData()
    {
        if (self::$DATA !== null) {
            return self::$DATA;
        } else {
            throw new \Exception('Config not initialized!'); 
        }
    }
}
