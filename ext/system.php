<?php

//load php extension
function load($ext,$or_ext = null){
	if (extension_loaded($ext)) return true;
	if (!function_exists('dl')){
		if (!(bool)ini_get('enable_dl') || (bool)ini_get('safe_mode')) return error('[system\dl] function dl() is disabled');
	}
	$ext = ((PHP_SHLIB_SUFFIX === 'dll')?'php_':'').$ext.'.'.PHP_SHLIB_SUFFIX;
	if (dl($ext)) return true;
	if (!(string)$or_ext) return error('loads extension '.$ext.' failed');
	$or_ext = ((PHP_SHLIB_SUFFIX === 'dll')?'php_':'').$or_ext.'.'.PHP_SHLIB_SUFFIX;
	if (dl($or_ext))
            return true;
        else
            return error('loads extension '.$or_ext.' failed');
}

function debug($level = E_ALL){
    ini_set('display_errors',true);
    error_reporting($level);
}

function nodebug() {
    ini_set('display_errors', false);
    error_reporting(0);
}

//操作session
function session($key, $value = null) {
    if ((!$session_Id = session_id()) && !headers_sent()) {
        session_start();
        $session_Id = session_id();
    }
    if ($value === '') {
        unset($_SESSION[$key]);
        return !isset($_SESSION[$key]);
    }
    if ($value === null)
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
        else
            return false;
    $_SESSION[$key] = $value;
    return isset($_SESSION[$key]);
}