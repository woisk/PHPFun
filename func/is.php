<?php

//是否是json
if (!function_exists('is_json')){
    function is_json($str){
        if (is_null(json_decode($str))
                || (json_last_error() != JSON_ERROR_NONE))
            return false;
        if (!preg_match('/^[\{\[].*[\}\]]$/', ltrim($str,'\\')))
            return false;
        return true;
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

/*检测是否有中文字符*/
function is_chinese($s){
    return (bool)preg_match('/[\x80-\xff]./', $s);
}

//是否是ACSII字符
function is_ascii($s){
    return (bool)preg_match('/^[\\x00-\\xFF]+$/', $s);
}

//是否是email地址
function is_email($s){
    return (bool)preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/',$s);
}

//是否是url
function is_url($s){
    return (bool)preg_match('/^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$/',$s);
}

//是否是域名
function is_domain($s){
    return (bool)preg_match('/^([\\w-]+\\.)+[\\w-]+$/', $s);
}

//是否是固定电话
function is_phone($s){
    return (bool)preg_match('/^\\d{3,4}[-]?\\d{7,8}(-\\d{3,4})?$/',$s);
}

//是否是国内手机号码
function is_mobile($s){
    return (bool)preg_match('/^(13|15|17)[0-9]{9}$/',$s);
}

//是否是邮编
function is_zipcode($s){
    return (bool)preg_match('/^\\d{6}$/',$s);
}

//是否是ipv4地址
function is_ipv4($s){
    return (bool)preg_match('/^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$/',$s);
}

//是否是电话号码(包括验证国内区号,国际区号,分机号)
function is_telephone($s){
    return (bool)preg_match('/^(([0\\+]\\d{2,3}-)?(0\\d{2,3})-)?(\\d{7,8})(-(\\d{3,}))?$/',$s);
}

//是否是纯数字
function is_number($s){
    return is_numeric($s) || ctype_digit($s);
}

//是否是纯英文字母
function is_char($s){
    return ctype_alpha($s);
}

//是否是小写字符
function is_lower($s){
    return ctype_lower($s);
}

//是否是大写字符
function is_upper($s){
    return ctype_upper($s);
}

//是否是中文格式日期，2015-3-20
function is_date($s){
    return (bool)preg_match('/^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$/',$s);
}

//长度是否小于提供的数值
function is_less_length($s,$length){
    if (!is_int($length))
        return false;
    return (length($s) < $length);
}

//是否是身份证号码
function is_idcard($s){
    return (bool)preg_match('/^[1-9]([0-9]{14}|[0-9]{17})$/',$s);
}