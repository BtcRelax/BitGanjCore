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

    /**
     * @return array
     * @throws Exception
     */
    public function getConfig(string $section):array
    {
        if (!\property_exists($section, $this->DATA)) {
                $errmsg = \sprintf('Unknown config section: %s', $section);
                \BtcRelax\Logger::general($errmsg, \BtcRelax\Logger::FATAL);
            }
        return $this->DATA[$section];
    }
    
    function __construct() {
        $cfgfile = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT') . '/config/config.ini';
        if (!file_exists($cfgfile)) {
            $errmsg =  \sprintf('Config file:%s does not exists!',$cfgfile);
            \BtcRelax\Logger::general($errmsg, \BtcRelax\Logger::FATAL);
        } else {
            $this->DATA = \parse_ini_file($cfgfile, true);
        }        
    }

        
}
