<?php

$GLOBALS['requires'] = array();
if (!function_exists('req_once')){
    function req_once($filename){
        global $requires;
        $filename = (string)$filename;
        if (empty($filename))
            return false;
        if (isset($requires[$filename]))
            return true;
        if (!is_file($filename))
            return false;
        $requires[$filename] = true;
        return require_once $filename;
    }
}

$GLOBALS['errors'] = array();
if (!function_exists('error')) {
    function error($msg = null) {
        global $errors;
        if ($msg === null)
            return $errors;
        $msg = (string) $msg;
        if (!empty($msg))
            array_push($errors, array('time' => time(), 'text' => $msg));
        return false;
    }

}

if (!function_exists('got')){
    function got($a,$k,$default = NULL){
        if (is_array($a) && isset($a[$k])) return $a[$k];
        if (is_object($a) && isset($a->$k)) return $a->$k;
        return $default;
    }
}

if (!function_exists('eq')){//获取数组中的第$index个元素
    function eq(array $haystack,$index){
        if (!is_int($index) || empty($haystack) || (count($haystack) < $index))
            return false;
        reset($haystack);
        $i = 0;
        while ($i<$index){
            $i++;
            next($haystack);
        }
        return current($haystack);
    }
}

if (!function_exists('pre')){
    function pre($var,$func = 'var_export'){
        echo '<pre style="text-align:left;clear:both;font-size:14px;color:black;">';
        if (function_exists($func))
            $func($var);
        echo '</pre>';
    }
}