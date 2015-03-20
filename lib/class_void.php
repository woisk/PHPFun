<?php

/**
 * 一个空类，对该类的实例做任何操作都只返回false  
 * 
 * @example
 * $void = new void();
 * $r = $void->set('key',123);
 * $r == false;
 */
class void {

    public function __construct() {
        ;
    }
    
    public function __destruct() {
        ;
    }
    
    public function __call($name, $arguments) {
        return false;
    }

    public static function __callStatic($name, $arguments) {
        return false;
    }

    public function __get($name) {
        return false;
    }
    
    public function __set($name, $value) {
        return false;
    }

    public function __isset($name) {
        return false;
    }
    
    public function __unset($name) {
        return false;
    }
    
    public function __sleep() {
        return '';
    }
    
    public function __wakeup() {
        return (object)array();
    }
    
    public function __toString() {
        return '';
    }
    
    public function __invoke() {
        return false;
    }
    
    public function __set_state($array) {
        return '';
    }
}