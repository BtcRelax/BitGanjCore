<?php 
namespace BtcRelax;

abstract class Base 
{
    private static $instance;

        
    protected static function Instantiate($class_name)
    {
        if (!isset(self::$instance)) {
            self::$instance = new $class_name;
            self::$instance->init();
        }
        return self::$instance;
    }

    abstract protected function init(); 
}