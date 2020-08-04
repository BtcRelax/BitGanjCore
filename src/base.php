<?php 
namespace BtcRelax;

abstract class Base 
{
    private static $instance;

        
    protected static function getIstance()
    {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
            self::$instance->init();
        }
        return self::$instance;
    }

    abstract protected function init(); 
}