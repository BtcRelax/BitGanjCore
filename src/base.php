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

    private function __clone()
    {
        trigger_error("Clonig not allowed");
    }
    
    abstract protected function init(); 
}