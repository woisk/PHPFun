<?php

//格式化文件大小 以KB MB GB为单位
function filesize_format($size) {

    if ($size / 1024 >= 1) {
        $size = sprintf("%.2f", $size / 1024);

        if ($size / 1024 >= 1) {
            $size = sprintf("%.2f", $size / 1024);
            if ($size / 1024 >= 1) {
                $size = sprintf("%.2f", $size / 1024);
                return $size . 'GB';
            } else {
                return $size . 'MB';
            }
        } else {
            return $size . 'KB';
        }
    } else {
        return $size . 'B';
    }
}

//例如返回5.2KB的字节数
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

function mv($oldname,$newname){
    if (!is_file($oldname))
        return error('oldfile '.$oldname.' missed');
    if (is_file($newname))
        return error('newfile '.$newname.' exists');
    return rename($oldname, $newname);
}

function del($filename){
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $r = @unlink($filename);
    if (is_file($filename))
        return error('delete file '.$filename.' failed');
    return $r;
}

function writeline($filename,$string) {
    if (!writable($filename))
        return false;
    $string = (string)$string;
    if (empty($string))
        return error('input string is empty');
    $file = fopen($filename, 'a+b');
    fwrite($file, $string . "\r\n");
    fclose($file);
    return true;
}

function clear($filename){
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $file = fopen($filename, 'w+b');
    fclose($file);
    return true;
}

function tmp($prefix = 'tmp', $dir = '/tmp') {
    $filename = tempnam($dir, $prefix);
    if (!$filename)
        return error('cannot create template file');
    if (!readable($filename))
        return false;
    return realpath($filename);
}

function download($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    $filesize = filesize($filename);
    $file = fopen($filename, 'rb');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . urlencode($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $filesize);
    ob_clean();
    flush();
    echo fread($file, $filesize);
}

function readable($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    if (is_readable($filename))
        return true;
    chmod($filename, 644);
    return is_readable($filename)?true:!error('[file\readable] file '.$filename.' is unreadable');
}

function writable($filename) {
    if (!is_file($filename))
        return error('target file '.$filename.' missed');
    if (is_writable($filename))
        return true;
    chmod($filename, 777);
    return is_writable($filename)?true:!error('[file\writable] file '.$filename.' is unwritable');
}

//获取不带扩展名的文件名
function fileprename($filename){
	return basename($filename,'.'.pathinfo($filename,PATHINFO_EXTENSION));
}

//获取文件扩展名
function fileext($filename,$check = false){
    if (is_file($filename)) {
        if ($check)
            return pathinfo($filename,PATHINFO_EXTENSION);
        $file = fopen($filename, "rb");
        $bin = fread($file, 2); //只读2字节  
        fclose($file);
    } else {
        $bin = substr($filename, 0, 2);
    }
    $strInfo = @unpack("C2chars", $bin);
    $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
    $fileType = '';
    switch ($typeCode) {
        case 7790:
            $fileType = 'exe';
            break;
        case 7784:
            $fileType = 'midi';
            break;
        case 8297:
            $fileType = 'rar';
            break;
        case 8075:
            $fileType = 'zip';
            break;
        case 255216:
            $fileType = 'jpg';
            break;
        case 7173:
            $fileType = 'gif';
            break;
        case 6677:
            $fileType = 'bmp';
            break;
        case 13780:
            $fileType = 'png';
            break;
        default:
            $fileType = '';
    }

    //Fix  
    if ($strInfo['chars1'] == '-1' AND $strInfo['chars2'] == '-40')
        return 'jpg';
    if ($strInfo['chars1'] == '-119' AND $strInfo['chars2'] == '80')
        return 'png';

    return $fileType;
}

//获取文件的mime类型
function filemime($filename) {
    if (!is_file($filename))
        return false;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $filemime = finfo_file($finfo, $filename);
        finfo_close($finfo);
    }else{
        $filemime = mime_content_type($filename);
    }
    return $filemime;
}

//根据mime获取扩展名
function mime2ext($mime){
    $mimes = mime();
    if (empty($mimes[$mime]))
        return '';
    return current(explode(' ',$mimes[$mime]));
}