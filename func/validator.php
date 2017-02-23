<?php
/**
 * 验证相关
 * @author wuxiao
 */

 /*
 * 函数名称：isName
 * 简要描述：姓名昵称合法性检查，只能输入中文英文
 * 输入：string
 * 输出：boolean
 */
function is_name($val) {
	if (preg_match("/^[\x80-\xffa-zA-Z0-9]{3,60}$/", $val)) {//2008-7-24
		return TRUE;
	}
	return FALSE;
}

 /*
 * 函数名称：isPostcode
 * 简要描述：检查输入的是否为邮编
 * 输入：string
 * 输出：boolean
 */
function is_postcode($val) {
	if (ereg("^[0-9]{4,6}$", $val))
		return TRUE;
	return FALSE;
}

/**
 * 判断数组是不是一个纯列表(和字典相反)
 * @param array $arr
 * @return type
 */
function is_list(array $arr){
    return array_keys($arr) === range(0,count($arr)-1);
}

/**
 * 判断数组是否是另一个数组的其中一段
 * @param array $needle 子数组
 * @param array $haystack 父数组
 * @return boolean
 */
function is_intersect(array $needle, array $haystack){
    $index = array_keys($haystack, current($needle));
    if (!$index){
        return false;
    }
    foreach ($index as $i){
        while ($v = next($needle)){
            if ($v != $haystack[++$i]){
                break;
            }
        }
        if (!$v){
            return true;
        }
        reset($needle);
    }
    
    return true;
}

if (!function_exists('is_json')){
    /**
     * 是否是json
     * @param string $s 判断的字串
     * @return boolean
     */
    function is_json($str){
        if (is_null(json_decode($str))
                || (json_last_error() != JSON_ERROR_NONE))
            return false;
        if (!preg_match('/^[\{\[].*[\}\]]$/', ltrim($str,'\\')))
            return false;
        return true;
    }
}

/**
 * 判断字符串是否是md5编码
 * @param string $s 判断的字串
 * @return boolean
 */
function is_md5($s) {
    return preg_match("/^[a-z0-9]{32}$/", $s);
}

/**
 * 判断字符串是否是utf8格式
 * @param string $word 判断的字串
 * @return boolean
 */
function is_utf8($word) {
    if (preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $word) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $word) == true || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $word) == true) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测是否有中文字符
 * @param string $s 判断的字串
 * @return boolean
 */
function is_chinese($s){
    return (bool)(preg_match('/[\x80-\xff]./', $s) || preg_match('/[\x{4e00}-\x{9fa5}]./', $s));
}

/*
 * 函数名称：isChinese
 * 简要描述：检查是否输入为汉字
 * 输入：string
 * 输出：boolean
 */
function is_chinese_v2($sInBuf) {
	$iLen = strlen($sInBuf);
	for ($i = 0; $i < $iLen; $i++) {
		if (ord($sInBuf{$i}) >= 0x80) {
			if ((ord($sInBuf{$i}) >= 0x81 && ord($sInBuf{$i}) <= 0xFE) && ((ord($sInBuf{$i + 1}) >= 0x40 && ord($sInBuf{$i + 1}) < 0x7E) || (ord($sInBuf{$i + 1}) > 0x7E && ord($sInBuf{$i + 1}) <= 0xFE))) {
				if (ord($sInBuf{$i}) > 0xA0 && ord($sInBuf{$i}) < 0xAA) {
					//有中文标点
					return FALSE;
				}
			} else {
				//有日文或其它文字
				return FALSE;
			}
			$i++;
		} else {
			return FALSE;
		}
	}
	return TRUE;
}

/**
 * 是否是ACSII字符，即单字节字符
 * @param string $s 判断的字串
 * @return boolean
 */
function is_ascii($s){
    return (bool)preg_match('/^[\\x00-\\x7F]+$/', $s);
}

/**
 * 是否是qq号
 * @param string $s 判断的字串
 * @return boolean
 */
function is_qq($s){
    return (bool)preg_match('/^[1-9][0-9]{4,}$/', $s);
}

/**
 * 是否是email地址
 * @param string $s 判断的字串
 * @return boolean
 */
function is_email($s){
    //return (bool)preg_match('/^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$/',$s);
    return (bool)filter_var($s,FILTER_VALIDATE_EMAIL);
}

/**
 * 是否是url
 * @param string $s 判断的字串
 * @return boolean
 */
function is_url($s){
    //return (bool)preg_match('/^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$/',$s);
    return (bool)filter_var($s,FILTER_VALIDATE_URL);
}

/**
 * 是否是域名
 * @param string $s 判断的字串
 * @return boolean
 */
function is_domain($s){
    return (bool)preg_match('/^([\\w-]+\\.)+[\\w-]+$/', $s);
}

/*
 * 函数名称:isDomain($Domain)
 * 简要描述:检查一个（英文）域名是否合法
 * 输入:string 域名
 * 输出:boolean
 */
function is_domain_v2($Domain) {
	if (!eregi("^[0-9a-z]+[0-9a-z\.-]+[0-9a-z]+$", $Domain)) {
		return FALSE;
	}
	if (!eregi("\.", $Domain)) {
		return FALSE;
	}

	if (eregi("\-\.", $Domain) or eregi("\-\-", $Domain) or eregi("\.\.", $Domain) or eregi("\.\-", $Domain)) {
		return FALSE;
	}

	$aDomain = explode(".", $Domain);
	if (!eregi("[a-zA-Z]", $aDomain[count($aDomain) - 1])) {
		return FALSE;
	}

	if (strlen($aDomain[0]) > 63 || strlen($aDomain[0]) < 1) {
		return FALSE;
	}
	return TRUE;
}

/**
 * 是否是固定电话
 * @param string $s 判断的字串
 * @return boolean
 */
function is_phone($s){
    return (bool)preg_match('/^\\d{3,4}[-]?\\d{7,8}(-\\d{3,4})?$/',$s);
}

/**
 * 是否是国内手机号码
 * @param string $s 判断的字串
 * @return boolean
 */
function is_mobile($s){
    return (bool)preg_match('/^(13|15|17)[0-9]{9}$/',$s);
}

 /*
 * 函数名称：isMobile
 * 简要描述：检查输入的是否为手机号
 * 输入：string
 * 输出：boolean
 */
function is_mobile_v2($val) {
	//该表达式可以验证那些不小心把连接符“-”写出“－”的或者下划线“_”的等等
	if (ereg("(^(\d{2,4}[-_－—]?)?\d{3,8}([-_－—]?\d{3,8})?([-_－—]?\d{1,7})?$)|(^0?1[35]\d{9}$)", $val))
		return TRUE;
	return FALSE;
}

/**
 * 是否是邮编
 * @param string $s 判断的字串
 * @return boolean
 */
function is_zipcode($s){
    return (bool)preg_match('/^[1-9]\d{5}(?!\d)$/',$s);
}

/**
 * 是否是ipv4地址
 * @param string $s 判断的字串
 * @return boolean
 */
function is_ipv4($s){
    return (bool)preg_match('/^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$/',$s);
}

/**
 * 是否是ip地址
 * @param string $s 判断的字串
 * @return boolean
 */
function is_ip($s){
    return (bool)filter_var($s,FILTER_VALIDATE_IP) && (bool)ip2long($val);
}

/**
 * 是否是网卡mac地址
 * @param string $s 判断的字串
 * @return boolean
 */
function is_mac($s){
    return (bool)filter_var($s,FILTER_VALIDATE_MAC);
}

/**
 * 是否是电话号码(包括验证国内区号,国际区号,分机号)
 * @param string $s 判断的字串
 * @return boolean
 */
function is_telephone($s){
    return (bool)preg_match('/^(([0\\+]\\d{2,3}-)?(0\\d{2,3})-)?(\\d{7,8})(-(\\d{3,}))?$/',$s);
}

/**
 * 是否是纯数字
 * @param string $s 判断的字串
 * @return boolean
 */
function is_number($s){
    return is_numeric($s) || ctype_digit($s);
}

/**
 * 是否是整数
 * @param string $s 判断的字串
 * @return boolean
 */
function is_integer_v2($s){
    return boolval(is_int($s) || preg_match('/^[-]?[ ]*\d+$/', $s));
}

/**
 * 是否是浮点数
 * @param string $s 判断的字串
 * @return boolean
 */
function is_float_v2($s){
    return boolval(is_float($s) || preg_match('/^[-]?[ ]*\d+\.\d+$/', $s));
}

/**
 * 是否是纯英文字母
 * @param string $s 判断的字串
 * @return boolean
 */
function is_char($s){
    return ctype_alpha($s);
}

/**
 * 是否是小写字符
 * @param string $s 判断的字串
 * @return boolean
 */
function is_lower($s){
    return ctype_lower($s);
}

/**
 * 是否是大写字符
 * @param string $s 判断的字串
 * @return boolean
 */
function is_upper($s){
    return ctype_upper($s);
}

/**
 * 是否是中文格式日期，2015-3-20
 * @param string $s 判断的字串
 * @return boolean
 */
function is_date($s){
    return (bool)preg_match('/^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$/',$s);
}

/*
 * 函数名称：isTime
 * 简要描述：检查日期是否符合0000-00-00 00:00:00
 * 输入：string
 * 输出：boolean
 */
function is_time($sTime) {
	if (ereg("^[0-9]{4}\-[][0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$", $sTime)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * 长度是否小于提供的数值
 * @param string $s 判断的字串
 * @param int $length 判断小于的长度
 * @return boolean
 */
function is_shorter_than($s,$length){
    if (!is_int($length))
        return false;
    return (length($s) < $length);
}

/**
 * 长度是否大于提供的数值
 * @param string $s 判断的字串
 * @param int $length 判断大于的长度
 * @return boolean
 */
function is_longer_than($s,$length){
    if (!is_int($length))
        return false;
    return (length($s) > $length);
}

/**
 * 大小是否大于提供的数值
 * @param string $s 判断的字串
 * @param int $num 判断大于的数值
 * @return boolean
 */
function is_greater_than($s,$num){
    if (!is_int($length))
        return false;
    $i = floatval($s);
    return $i > $num;
}

/**
 * 大小是否小于提供的数值
 * @param string $s 判断的字串
 * @param int $num 判断小于的数值
 * @return boolean
 */
function is_less_than($s,$num){
    if (!is_int($length))
        return false;
    $i = floatval($s);
    return $i < $num;
}

/**
 * 是否是身份证号码
 * @param string $s 判断的字串
 * @return boolean
 */
function is_idcard($s){
    return (bool)preg_match('/^[1-9]([0-9]{14}|[0-9]{17})$/',$s);
}

/**
 * 是否是台湾身份证号码
 * @param string $s 判断的字串
 * @return boolean
 */
function is_tw_idcard($s){
    return (bool)preg_match('/^^(?:\d{15}|\d{18})$$/',$s);
}

/**
 * 按照传入的验证规则进行批量数据验证
 * @param array $rules 传入的验证规则
 * @param array $data 待验证的数据
 * @param array $customMessages 自定义校验提示
 * @return boolean
 * @example ../lib/validator/demo.php
 */
function validator(array $rules, array $data = null,array $customMessages = null) {
        $validator = Validator::make($rules, $data);
        if ($customMessages)
                Validator::setMessage($customMessages);
        if ($validator->fails()){
                return $validator->messages();
        }else{
                return true;
        }
}