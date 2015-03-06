<?php

/**
 * Description of phpfun
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
defined('FUN_EXT') or define('FUN_EXT',  FUN_ROOT.'ext'.DS);

//date_default_timezone_set('PRC');

class PHPFun{
    
    protected $debug = true;
    
    public function init(){
        require_once FUN_ROOT.'init.php';
        
        $libs = glob(FUN_EXT.'*.php');
        $libs = array_map('realpath',$libs);
        array_walk($libs,'req_once');
        
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
        $file = FUN_LIB.'class_'.strtolower($name).EXT;
        if (!is_file($file))
            return false;
        include_once $file;
    }
}