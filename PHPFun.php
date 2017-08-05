<?php

/**
 * php大量自定义的方便的函数
 *
 * @author wuxiao
 * @date 20140626
 */

defined('DS') or define('DS',DIRECTORY_SEPARATOR);
defined('EXT') or define('EXT','.php');
defined('FUN_ROOT') or define('FUN_ROOT',  realpath(dirname(__FILE__)).DS);

defined('FUN_CACHE') or define('FUN_CACHE',  FUN_ROOT.'runtime'.DS.'cache'.DS);
defined('FUN_LOG') or define('FUN_LOG',  FUN_ROOT.'runtime'.DS.'log'.DS);

defined('FUN_LIB') or define('FUN_LIB',  FUN_ROOT.'lib'.DS);
defined('FUN_FUNC') or define('FUN_FUNC',  FUN_ROOT.'func'.DS);

defined('FUN_KEY') or define('FUN_KEY',  '1d2sbFIHzJLmaCSIB8aVsdPj2teSEvUqvxY7femSB2OLsbXs12DMlbPe');
//date_default_timezone_set('Asia/Shanghai');

class PHPFun{
    
    protected $debug = true;
    
    protected static $_instance;
    
    private function __construct() {
        $this->init(func_get_args());
        self::$_instance = $this;
    }
    
    public static function instance(){
        if (!is_a(self::$_instance,__CLASS__)){
            self::$_instance = new self(' ');
        }
        self::$_instance->init(func_get_args());
        return self::$_instance;
    }
    
    public static function load($namespace){
        $namespace = basename($namespace, EXT);
        $namespace = FUN_FUNC . $namespace . EXT;
        return req_once($namespace);
    }
    
    private function init(array $namespaces = array()){
        require_once FUN_ROOT.'init.php';
        
        if (empty($namespaces)){
            $libs = glob(FUN_FUNC.'*.php');
            array_walk($libs,'req_once');
        }else{
            foreach ($namespaces as $namespace){
                $this->load($namespace);
            }
        }
        
        spl_autoload_register(array($this,'lib_autoload'));
    }
    
    //php debug开启
    public function debug(){
        $this->debug = true;
        ini_set('display_errors', true);
        error_reporting(E_ALL);
    }
    
    //php debug关闭
    public function nodebug(){
        $this->debug = false;
        ini_set('display_errors', false);
        error_reporting(0);
    }
    
    private function lib_autoload($name){
        $name = strtolower($name);
        $file = FUN_LIB.'class_'.$name.EXT;
        if (!is_file($file)){
                if (!is_file($file = FUN_LIB.$name.DS.$name.EXT)){
                        return false;
                }
        }
        include_once $file;
    }
}