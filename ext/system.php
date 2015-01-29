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

function cache($key, $value = null,$lifetime = 3600){
    $cache = new Cached;
    if ($value === '') {
        return $cache->delete($key);
    }
    if ($value === null){
        return $cache->get($key);
    }
    return $cache->set($key, $value,$lifetime);
}

/*
* 从网络传输文件
*/
function wget($inputfile,$outputfile)
{
   //在线获取文件类型
   $onlinefiletype = online_filetype($inputfile);

   //wget续传抓取文件
   $command = "wget -c '".escapeshellcmd($inputfile)."' -O '{$outputfile}' -T 120 -t 5";
   exec($command, $result , $retval);
   /*if (defined('cmd_render') && !cmd_render){
       $command .= ' 2>&1 >/dev/null';
       echo $command,"\n";
   }*/
   if ($retval)
       return implode("\n",$result);
   if (!is_file($outputfile))
       return "{$outputfile} not exists";

   //获取抓取下来的文件类型
   $finfo  =  finfo_open ( FILEINFO_MIME_TYPE );
   $localfiletype = finfo_file ( $finfo ,  $outputfile );
   finfo_close ( $finfo );

   if ($onlinefiletype && ($onlinefiletype != $localfiletype))
       return "online {$onlinefiletype} not match local {$localfiletype}";
   return 0;
}