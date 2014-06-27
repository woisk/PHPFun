<?php

if (!function_exists('is_json')){
    function is_json($str){
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

// Returns true if $string is valid UTF-8 and false otherwise.
function is_utf8($word) {
    if (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $word) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $word) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $word) == true) {
        return true;
    } else {
        return false;
    }
}
// function is_utf8

/*检测是否有中文字符*/
function is_chinese($s){
         return preg_match('/[\x80-\xff]./', $s);
}