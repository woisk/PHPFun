<?php
/**
 * 字符串操作
 * @author wuxiao
 */

/**
 * 去除一般存储无法识别的字符串，留下可以识别的
 * @param string $str
 */
function remove_unrecognized($str){
    $blacklist = array();
    
    $tmp = array();
    for ($i = 0x83;$i<0xbe;$i++){
        $tmp[] = chr(0xf0).chr(0x9f).chr(0x94).chr($i);
    }
    
    $blacklist = array_merge($blacklist, $tmp);
    
    return str_replace($blacklist, '', $str);
}

/**
 * 找出两个字符串的交集
 * @param string $string1 字符串一
 * @param string $string2 字符串二
 * @return string
 */
function str_intersect($string1, $string2){
    $array1 = array();
    for ($i=0;$i<strlen($string1);$i++){
        $array1[] = substr($string1,$i,1);
    }
    $array2 = array();
    for ($i=0;$i<strlen($string2);$i++){
        $array2[] = substr($string2,$i,1);
    }
    
    $intersect = array();
    $start = 0;
    do{
        $len = 1;
        do{
            $tmp = array_slice($array1, $start, $len);
            if (!is_intersect($tmp,$array2)){
                break;
            }
            $len++;
        }while($start + $len <= count($array1));
        $intersect = array_merge($intersect,$tmp);
        $start += $len;

    }while($start < count($array1));
    
    $str_intersect = '';
    for ($i=0;$i<count($intersect);$i++){
        $str_intersect .= $intersect[$i];
    }
    return $str_intersect;
}

/**
 * 获取字符串长度
 * @param string $str
 * @return int
 */
function length($str) {
    $str = (string)$str;
    if (function_exists('mb_strlen'))
        return mb_strlen($str);
    if (is_utf8($str)){
        return strlen($str);
    }
	$count = 0;
	for($i = 0; $i < strlen($str); $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
	    	}
    		$count++;
	}
	return $count;
}

/**
 * 返回随机字符串，数字/大写字母/小写字母
 * @param int $len
 * @param enum $format ALL|CHAR|NUMBER
 * @return string
 */
function random($len = 6, $format = 'ALL') {
	switch (strtoupper($format)) {
		case 'ALL' :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break;
		case 'CHAR' :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			break;
		case 'NUMBER' :
			$chars = '0123456789';
			break;
		default :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			break;
	}
	$string = "";
	while ( strlen ( $string ) < $len )
		$string .= substr ( $chars, (mt_rand () % strlen ( $chars )), 1 );
	return $string;
}

/**
 * 截取一定长度的字符串，确保截取后字符串不出乱码
 * @param string $string
 * @param int $length
 * @param string $dot
 * @param string $charset
 * @return string
 */
function cutstr($string, $length, $dot = ' ...', $charset = 'utf-8') {
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';
	if(strtolower($charset) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		$_length = $length - 1;
		for($i = 0; $i < $length; $i++) {
			if(ord($string[$i]) <= 127) {
				$strcut .= $string[$i];
			} else if($i < $_length) {
				$strcut .= $string[$i].$string[++$i];
			}
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return $strcut.$dot;
}

/**
 * 来自destoon的字符串截取函数，
 * @param string $string
 * @param int $length
 * @param int $start
 * @param string $suffix
 * @param string $charset
 * @return string
 */
function dsubstr($string, $length, $start = 0, $suffix = '', $charset = 'UTF-8') {
	if($start = intval($start)) {
		$tmp = dsubstr($string, $start);
		$string = substr($string, strlen($tmp));
	}
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array('&quot;', '&lt;', '&gt;'), array('"', '<', '>'), $string);
	$length = $length - strlen($suffix);
	$str = '';
	if(strtolower($charset) == 'utf-8') {
		$n = $tn = $noc = 0;
		while($n < $strlen)	{
			$t = ord($string{$n});
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) break;
		}
		if($noc > $length) $n -= $tn;
		$str = substr($string, 0, $n);
	} else {
		for($i = 0; $i < $length; $i++) {
			$str .= ord($string{$i}) > 127 ? $string{$i}.$string{++$i} : $string{$i};
		}
	}
	$str = str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), $str);
	return $str == $string ? $str : $str.$suffix;
}

/**
 * 将内容进行UNICODE编码，编码后的内容格式：YOKA\王 （原始：YOKA王）
 * @param string $s
 * @return string
 */
function unicodeencode($s) {
	$ss = @iconv ( 'UTF-8', 'UCS-2', $s );
    $s = $ss ? $ss : $s;
	$len = strlen ( $s );
	$str = '';
	for($i = 0; $i < $len - 1; $i = $i + 2) {
		$c = $s [$i];
		$c2 = $s [$i + 1];
		if (ord ( $c ) > 0) { // 两个字节的文字
			$str .= '\u' . base_convert ( ord ( $c ), 10, 16 ) . base_convert ( ord ( $c2 ), 10, 16 );
		} else {
			$str .= $c2;
		}
	}
	return $str;
}

/**
 * unicode解码
 * @param string $str
 * @return string
 */
function unicodedecode($str) {
	preg_match_all ( '/\\\u([[:alnum:]]{4})/', $str, $a );
	foreach ( $a [1] as $uniord ) {
		$dec = hexdec ( $uniord );
		$utf = '';
		if ($dec < 128) {
			$utf = chr ( $dec );
		} else if ($dec < 2048) {
			$utf = chr ( 192 + (($dec - ($dec % 64)) / 64) );
			$utf .= chr ( 128 + ($dec % 64) );
		} else {
			$utf = chr ( 224 + (($dec - ($dec % 4096)) / 4096) );
			$utf .= chr ( 128 + ((($dec % 4096) - ($dec % 64)) / 64) );
			$utf .= chr ( 128 + ($dec % 64) );
		}
		$str = str_replace ( '\u' . $uniord, $utf, $str );
	}
	return ($str);
}

/**
 * xml编码, array => xml
 * @param array $array
 * @param string $main
 * @return string
 */
function xmlencode(array $array,$main = 'xml'){
    $xml = '<?xml version="1.0"?>'."\r\n";
    $xml .= '<'.$main.'>'."\r\n";
    $xml .= (string)_xmlencode($array);
    $xml .= '</'.$main.'>'."\r\n";
    
    return $xml;
}
/**
 * xmlencode子函数
 * @param array $data
 * @return string
 */
function _xmlencode($data){
   $t = '';
   foreach($data as $key=>$value){
       if (preg_match('/^[\d]+$|[\W]+/', $key))
               return error('[string\xmlencode] invalid element name with value of '.serialize($value));
       if (is_array($value))
           $t .= '<'.$key.'>'."\r\n"._xmlencode($value).'</'.$key.'>'."\r\n";
       elseif(is_null($value) || empty($value))
           $t .= '<'.$key.' />'."\r\n";
       else
           $t .= '<'.$key.'>'.$value.'</'.$key.'>'."\r\n";
   }
   return $t;
};
/*
 * END xml编码
 */

/**
 * xml解码, xml => array
 * @param string $str
 * @return array
 */
function xmldecode($str){
    $data = simplexml_load_string($str);
    
    $data = _xmldecode($data);
    return $data;
}
/**
 * xmldecode子函数
 * @param array $data
 * @return array
 */
function _xmldecode($data){
    $data = get_object_vars($data);
    foreach ($data as &$node){
        if (is_object($node))
            $node = _xmldecode($node);
    }
    return $data;
}
/*
 * END xml解码
 */

/**
 * xml档案转换到数组, xml => array
 * @author http://us3.php.net/manual/en/function.xml-parse.php
 * @param string $target 解析对象，可以是文件路径/URL地址
 * @param int $get_attributes
 * @param string $priority
 * @return array
 */
function xml2array($target, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create'))
    {
        return array ();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($target, 'rb')))
    {
        return array ();
    }
    while (!feof($fp))
    {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array ();
    foreach ($xml_values as $data)
    {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value))
        {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes)
        {
            foreach ($attributes as $attr => $val)
            {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open")
        {
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current))))
            {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else
            {
                if (isset ($current[$tag][0]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr']))
                    {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete")
        {
            if (!isset ($current[$tag]))
            {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else
            {
                if (isset ($current[$tag][0]) and is_array($current[$tag]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data)
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes)
                    {
                        if (isset ($current[$tag . '_attr']))
                        {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        elseif ($type == 'close')
        {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}

/**
 * 返回字符串的拼音
 * @param string $_String
 * @param string $_Code
 * @return string
 */
function pinyin($_String, $_Code = 'UTF8') { //GBK页面可改为gb2312，其他随意填写为UTF8
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
            "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
            "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
            "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
            "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
            "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
            "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
            "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
            "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
            "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
            "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
            "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
            "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
            "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
            "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
            "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
            "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
            "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
            "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
            "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
            "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
            "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
            "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
            "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
            "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
            "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
            "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
            "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
            "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
            "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
            "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
            "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
            "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
            "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
            "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
            "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
            "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
            "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
            "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
            "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
            "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
            "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = array_combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312')
        $_String = _U2_Utf8_Gb($_String);
    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++) {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160) {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data) ? : _Dict($_P);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}
/**
 * pinyin子函数
 * @param mixed $_Num
 * @return mixed
 */
function _Dict($_Num) {
    $_Dict = array(-2354=>'xin');
    return isset($_Dict[$_Num]) ? $_Dict[$_Num] : '';
}
/**
 * pinyin子函数
 * @param mixed $_Num
 * @param mixed $_Data
 * @return mixed
 */
function _Pinyin($_Num, $_Data) {
    if ($_Num > 0 && $_Num < 160) {
        return chr($_Num);
    } elseif ($_Num < -20319 || $_Num > -10247) {
        return '';
    } else {
        foreach ($_Data as $k => $v) {
            if ($v <= $_Num)
                break;
        }
        return $k;
    }
}
/**
 * pinyin子函数
 * @param mixed $_C
 * @return mixed
 */
function _U2_Utf8_Gb($_C) {
    $_String = '';
    if ($_C < 0x80) {
        $_String .= $_C;
    } elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GBK//IGNORE', $_String);
}
/*
 * END 返回字符串的拼音
 */

/**
 * 获取字符串首字母, 可传入汉字，字母 ，数字
 *
 * @param String $string
 * @return String
 */
function getFirstLetter($string) {
    $string = iconv('utf-8', 'gbk', $string); //字符编码转换
    $dict = array(
        'a' => 0xB0C4,
        'b' => 0xB2C0,
        'c' => 0xB4ED,
        'd' => 0xB6E9,
        'e' => 0xB7A1,
        'f' => 0xB8C0,
        'g' => 0xB9FD,
        'h' => 0xBBF6,
        'j' => 0xBFA5,
        'k' => 0xC0AB,
        'l' => 0xC2E7,
        'm' => 0xC4C2,
        'n' => 0xC5B5,
        'o' => 0xC5BD,
        'p' => 0xC6D9,
        'q' => 0xC8BA,
        'r' => 0xC8F5,
        's' => 0xCBF9,
        't' => 0xCDD9,
        'w' => 0xCEF3,
        'x' => 0xD188,
        'y' => 0xD4D0,
        'z' => 0xD7F9
    );
    $letter = substr($string, 0, 4);
    if ($letter >= chr(0x81) && $letter <= chr(0xfe)) {
        $num = hexdec(bin2hex(substr($string, 0, 2)));
        foreach ($dict as $k => $v) {
            if ($v >= $num)
                break;
        }
        return strtoupper($k);
    }
    elseif ((ord($letter) > 64 && ord($letter) < 91) || (ord($letter) > 96 && ord($letter) < 123)) {
        return strtoupper($letter{0});
    } elseif ($letter >= '0' && $letter <= '9') {
        return $letter;
    } elseif (is_numeric($letter)) {
        return substr($letter, 0, 1);
    } else {
        return false;
    }
}

/**
 * 获取货币
 * 
 * @param string $currency
 * @return string
 */
function getCurrency($currency) {
    switch ($currency) {
        case 'BRL' : $currency_symbol = '&#82;&#36;';
            break;
        case 'AUD' : $currency_symbol = '&#36;';
            break;
        case 'CAD' : $currency_symbol = '&#36;';
            break;
        case 'MXN' : $currency_symbol = '&#36;';
            break;
        case 'NZD' : $currency_symbol = '&#36;';
            break;
        case 'HKD' : $currency_symbol = '&#36;';
            break;
        case 'SGD' : $currency_symbol = '&#36;';
            break;
        case 'USD' : $currency_symbol = '&#36;';
            break;
        case 'EUR' : $currency_symbol = '&euro;';
            break;
        case 'CNY' : $currency_symbol = '&yen;';
            break;
        case 'JPY' : $currency_symbol = '&yen;';
            break;
        case 'TRY' : $currency_symbol = '&#84;&#76;';
            break;
        case 'NOK' : $currency_symbol = '&#107;&#114;';
            break;
        case 'ZAR' : $currency_symbol = '&#82;';
            break;
        case 'CZK' : $currency_symbol = '&#75;&#269;';
            break;
        case 'MYR' : $currency_symbol = '&#82;&#77;';
            break;
        case 'DKK' : $currency_symbol = '&#107;&#114;';
            break;
        case 'HUF' : $currency_symbol = '&#70;&#116;';
            break;
        case 'ILS' : $currency_symbol = '&#8362;';
            break;
        case 'PHP' : $currency_symbol = '&#8369;';
            break;
        case 'PLN' : $currency_symbol = '&#122;&#322;';
            break;
        case 'SEK' : $currency_symbol = '&#107;&#114;';
            break;
        case 'CHF' : $currency_symbol = '&#67;&#72;&#70;';
            break;
        case 'TWD' : $currency_symbol = '&#78;&#84;&#36;';
            break;
        case 'THB' : $currency_symbol = '&#3647;';
            break;
        case 'GBP' : $currency_symbol = '&pound;';
            break;
        default : $currency_symbol = '&yen;';
            break;
    }
    return $currency_symbol;
}

/**
 * 全角字符转换为半角 
 * @param string $str 传入要转换的字符串
 * @param enum $sbc2dbc 0：全角转半角， 1： 半角转全角
 * @return string
 */
function dbc2sbc($str, $sbc2dbc = 0) {
        $dbc2sbc = array (
                '０' => '0','１' => '1','２' => '2', '３' => '3',
                '４' => '4','５' => '5','６' => '6','７' => '7',
                '８' => '8','９' => '9','Ａ' => 'A','Ｂ' => 'B',
                'Ｃ' => 'C','Ｄ' => 'D','Ｅ' => 'E','Ｆ' => 'F',
                'Ｇ' => 'G','Ｈ' => 'H','Ｉ' => 'I','Ｊ' => 'J',
                'Ｋ' => 'K','Ｌ' => 'L','Ｍ' => 'M','Ｎ' => 'N',
                'Ｏ' => 'O','Ｐ' => 'P','Ｑ' => 'Q','Ｒ' => 'R',
                'Ｓ' => 'S','Ｔ' => 'T','Ｕ' => 'U','Ｖ' => 'V',
                'Ｗ' => 'W','Ｘ' => 'X','Ｙ' => 'Y','Ｚ' => 'Z',
                'ａ' => 'a','ｂ' => 'b','ｃ' => 'c','ｄ' => 'd',
                'ｅ' => 'e','ｆ' => 'f','ｇ' => 'g','ｈ' => 'h',
                'ｉ' => 'i','ｊ' => 'j','ｋ' => 'k','ｌ' => 'l',
                'ｍ' => 'm','ｎ' => 'n','ｏ' => 'o','ｐ' => 'p',
                'ｑ' => 'q','ｒ' => 'r','ｓ' => 's','ｔ' => 't',
                'ｕ' => 'u','ｖ' => 'v','ｗ' => 'w','ｘ' => 'x',
                'ｙ' => 'y','ｚ' => 'z','－' => '-','　' => ' ',
                '：' => ':','．' => '.','，' => ',','／' => '/',
                '％' => '%','＃' => '#','！' => '!','＠' => '@',
                '＆' => '&','（' => '(','）' => ')','＜' => '<',
                '＞' => '>','＂' => '"','＇' => '\'','？' => '?',
                '［' => '[','］' => ']','｛' => '{','｝' => '}',
                '＼' => '\\','｜' => '|','＋' => '+','＝' => '=',
                '＿' => '_','＾' => '^','￥' => '$','￣' => '~',
                '｀' => '`',
        );
        if ($sbc2dbc){
                $dbc2sbc = array_flip($dbc2sbc);
        }
        return strtr($str, $arr); 
}