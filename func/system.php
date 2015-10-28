<?php

/**
 * 载入php扩展
 * @param string $ext
 * @param string $or_ext
 * @return boolean
 * @example load('redis);
 */
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

/**
 * 开启php错误提示
 * @param type $level
 */
function debug($level = E_ALL){
    ini_set('display_errors',true);
    error_reporting($level);
}

/**
 * 关闭php错误提示
 */
function nodebug() {
    ini_set('display_errors', false);
    error_reporting(0);
}

/**
 * 操作session
 * @param type $key
 * @param type $value
 */
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

/**
 * 存储/获取缓存
 * @param type $key
 * @param type $value
 * @param type $lifetime
 * @return type
 */
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

/**
 * 从网络传输文件，直接使用linux的wget命令
 * @param type $inputfile 来源地址
 * @param type $outputfile 输出文件路径
 * @return int
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
   $localfiletype = filemime($outputfile);

   if ($onlinefiletype && ($onlinefiletype != $localfiletype))
       return "online {$onlinefiletype} not match local {$localfiletype}";
   return 0;
}

/*
 * 判断当前是否在命令行模式下
 */
function is_cmd(){
    return (PHP_SAPI === 'cli') or isset($_SERVER['argv']);
}

/**
 * 添加非守护进程
 * @return type
 */
function fork(){
    $child_threads = func_get_args();
    $t = new Thread;
    foreach ($child_threads as $child_thread){
        $t->fork($child_thread);
    }
    $t->run();
    return $t->getpid();
}

/**
 * 添加守护进程
 * @return type
 */
function daemon(){
    $child_threads = func_get_args();
    $t = new Thread;
    foreach ($child_threads as $child_thread){
        $t->daemon($child_thread);
    }
    $t->run();
    return $t->getpid();
}