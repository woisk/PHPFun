<?php
/**
 * ftp
 * @author wuxiao
 */

/**
 * 连接ftp
 * 
 * $ftp = 'ftp://user:pass@192.168.99.1:21'
 * @param string $ftp ftp连接url
 * @return boolean
 */
function ftp_connect_v2($ftp){
    if (!preg_match('/^ftp:\/\/[^:]+:[^@]+@[\d\.]+(:\d+)?$/i', $ftp)
            || (!$ftp = parse_url($ftp))){
        return false;
    }elseif (@$ftp['scheme'] != 'ftp'){
        return false;
    }elseif (empty($ftp['host'])){
        return false;
    }else{
        $ftp += array(
            'port'=>21,
            'user'=>'',
            'pass'=>''
        );
    }
    
    $fp = FTP::instance($ftp['host'], $ftp['port'], $ftp['user'], $ftp['pass']);
    if (!$fp->connect()){
        return $fp->error();
    }
    return $fp;
}

/**
 * 上传文件到ftp
 * 
 * $ftp = 'ftp://username:password@115.216.196.218:21';
 * 
 * $ret = ftp_upload($ftp,'/data/bak/test.js','/data/www/ucenter/data/test123.js');
 * @param string $ftp ftp连接url
 * @param string $source 本地文件路径
 * @param string $target 上传目标文件，可带ftp目标路径
 * @return boolean
 */
function ftp_upload($ftp,$source,$target){
    if (!is_object($fp = ftp_connect_v2($ftp)))
        return $fp;
    
    $ret = $fp->upload($source,$target);
    $fp->ftp_close();
    return $ret;
}

/**
 * 从ftp删除文件
 * 
 * $ftp = 'ftp://username:password@115.216.196.218:21';
 * 
 * ftp_upload($ftp,'/data/www/ucenter/data/test123.js');
 * @param string $ftp ftp连接url
 * @param string $path 文件路径
 * @return boolean
 */
function ftp_del($ftp, $path){
    if (!is_object($fp = ftp_connect_v2($ftp)))
        return $fp;
    
    $dirname = dirname($path);
    $filename = basename($path);
    if (!$fp->ftp_chdir($dirname)){
        return false;
    }
    $ret = $fp->ftp_delete($filename);
    $fp->ftp_close();
    return $ret;
}