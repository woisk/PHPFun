<?php

if (FUN_DEBUG){
    ini_set('display_errors', true);
    error_reporting(E_ALL);
}else{
    ini_set('display_errors', false);
    error_reporting(0);
}

require_once FUN_LIB.'common.php';
$libs = glob(FUN_EXT.'*.php');
$libs = array_map('realpath',$libs);
array_walk($libs,'req_once');

//auto_load
if (!function_exists('lib_autoload')){
    function lib_autoload($name){
        $file = FUN_CLASS.'class_'.strtolower($name).EXT;
        if (!is_file($file))
            return false;
        include_once $file;
    }
}
spl_autoload_register('lib_autoload');