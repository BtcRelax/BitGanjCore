<?php

namespace BtcRelax;

use Exception;

/**
 * Application configuration.
 */
final class Config
{

    /** @var array config data */
    private $DATA = null;
    private static $instance;

    public static function getIstance(): \BtcRelax\Config
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->init();
        }
        return self::$instance;
    }
    
    
    /**
     * @return array
     * @throws Exception
     */
    public function getConfig($section = null)
    {
        if (empty($section)) { 
                return $this->DATA;
            }
        if (!\array_key_exists($section, $this->DATA)) {
                throw new \Exception (\sprintf('Unknown config section: %s', $section));
            }
        return $this->DATA[$section];
    }
        
    private function init()
    {
        $cfgfile = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT') . '/config/config.ini';
        if (!file_exists($cfgfile)) {
            throw new \Exception(\sprintf('Config file:%s does not exists!',$cfgfile));
        } else {
            $this->DATA = \parse_ini_file($cfgfile, true);
        }
    }
        
}
