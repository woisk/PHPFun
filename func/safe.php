<?php

/**
 * 来自ucenter的加/解密函数
 * @param type $string
 * @param type $operation
 * @param type $key
 * @param type $expiry
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
 * @param type $value
 * @param type $key
 * @return boolean
 */
function DOkillinject(&$value, $key) {
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
 * @param String $password_str 密码字符串
 * @return float
 * 
 * Returns a float between 0 and 100. The closer the number is to 100 the
 * the stronger password is; further from 100 the weaker the password is.
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