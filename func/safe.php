<?php
/**
 * 安全和防护
 * @author wuxiao
 */

/**
 * 生成唯一值uuiid
 * @return string
 */
function uuid() {
    if (function_exists ( 'com_create_guid' )) {
        return com_create_guid ();
    } else {
        mt_srand ( ( double ) microtime () * 10000 ); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
        $charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) ); //根据当前时间（微秒计）生成唯一id.
        $hyphen = chr ( 45 ); // "-"
        $uuid = '' . //chr(123)// "{"
        substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
        //.chr(125);// "}"
        return $uuid;
    }
}

/**
 * 字符代换加密/解密
 * @param $string
 * @param string $operation
 * @return mixed
 */
function sub_crypt($string, $operation = 'ENCODE') {
    $source = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $dest = 'syxV4uTeRqLAltoOvnhSDUd3bWZQBf1Y7XKgHm25MzICGkNJwaFjr8Pc0i96pE';
    if ($operation != 'ENCODE'){
        list($source,$dest) = array($dest,$source);
    }
    for ($i=0;$i<strlen($string);$i++){
        $pos = strpos($source,$string[$i]);
        $string[$i] = $dest[$pos];
    }
    return $string;
}

/**
 * 来自ucenter的加/解密函数
 * @param string $string 要加/解密的字符串
 * @param enum $operation 操作类型，DECODE：解密，ENCODE：加密
 * @param string $key 自定义盐
 * @param int $expiry 加密结果过期时间
 * @return string
 */
function uc_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;

	$key = md5($key ? $key : FUN_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
				return '';
			}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

/**
 * 注入预防
 * @param string $value 要检验的数据
 * @return boolean
 */
function DOkillinject(&$value) {
    if (!is_string($value))
        return false;

    if (true) {
        if (false !== strpos($value, '<script')) {
            die('未知错误，系统运行中止');
        }
        if (false !== strpos($value, 'script>')) {
            die('未知错误，系统运行中止');
        }
        if (false !== strpos($value, 'user()')) {
            die('未知错误，系统运行中止');
        }
        if (false !== strpos($value, 'database()')) {
            die('未知错误，系统运行中止');
        }
        if (false !== strpos($value, 'version()')) {
            die('未知错误，系统运行中止');
        }
        if (false !== strpos($value, 'information_schema')) {
            die('未知错误，系统运行中止');
        }
    }

    $value = htmlspecialchars(htmlspecialchars_decode(trim($value)));
    return true;
}

/**
 * 计算密码字符串的强弱度，100分满分
 * 
 * Returns a float between 0 and 100. The closer the number is to 100 the
 * 
 * the stronger password is; further from 100 the weaker the password is.
 * @param string $password_str 密码字符串
 * @return float
 */
function password_strength($password_str){
    $h    = 0;
    $size = strlen($password_str);
    foreach(count_chars($password_str, 1) as $v){
        $p = $v / $size;
        $h -= $p * log($p) / log(2);
    }
    $strength = ($h / 4) * 100;
    if($strength > 100){
        $strength = 100;
    }
    return intval($strength);
} 

/**
 * RSA加密
 * @param string $data 数据原文
 * @param mixed $private_key 私钥文件/文本
 * @return string 密文
 */
function rsa_encrypt($data,$private_key){
    if (empty($private_key)){
        return false;
    }
    if (is_file($private_key)){
        $private_key = file_get_contents($private_key);
    }
    $private_key = openssl_pkey_get_private($private_key);
    if (!$private_key){
        return false;
    }
    openssl_private_encrypt($data,$encrypted,$private_key);
    return $encrypted ? base64_encode($encrypted) : false;
}

/**
 * RSA解密
 * @param string $encrypted 密文
 * @param mixed $public_key 公钥文件/文本
 * @return string 原文
 */
function rsa_decrypt($encrypted,$public_key){
    if (empty($public_key)){
        return false;
    }
    if (is_file($public_key)){
        $public_key = file_get_contents($public_key);
    }
    $public_key = openssl_pkey_get_public($public_key);
    if (!$public_key){
        return false;
    }
    $encrypted = base64_decode($encrypted);
    openssl_public_decrypt($encrypted,$decrypted,$public_key);
    return $decrypted ? $decrypted : false;
}
