<?php

/**
 * 弹出提示
 * @param type $msg 提示文字
 * @param type $url 弹出提示后跳转页面
 */
function alert($msg = null, $url = null) {
    $session_key = 'alert_message';
    //echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    headers_sent() or header('Content-type: text/html; charset=utf-8');
    if (($msg === null) && ($url === null)) {
        if ($alert = session($session_key))
            echo '<script>alert("' . $alert . '");</script>';
        session($session_key, '');
        return true;
    }
    if ($url)
        session($session_key, $msg);
    else
        echo '<script>alert("' . $msg . '");</script>';
    if ($url) {
        echo '<script>location.href="' . $url . '";</script>';
        exit;
    }
}

/**
 * 延迟跳转
 * @param type $url 跳转地址
 * @param type $timeout 延迟时间，秒为单位
 * @param type $msg 跳转前显示的提示文字
 */
function delay($url = '/',$timeout = 5,$msg = ''){
    if (intval($timeout) <= 0)
        return error('wrong timeout setting <'.$timeout.'>');
    if ($msg)
        echo "\r\n<br />",$msg;
    echo "<br />\r\n";
    echo '<span id="delaySecond">'.(int)$timeout.'</span>秒后跳转......';
    echo '<script>var delay='.(int)$timeout.';setInterval(function(){
        document.getElementById("delaySecond").innerHTML=--delay;
        if (delay<=0) location.href="'.$url.'";
    },1000)</script>';
}

/**
 * $format生成验证码图像
 * @param string $format 随机字符串类型，ALL：大小写英文、数字，CHAR：大小写英文，NUMBER：纯数字
 */
function verifycode($format = ''){
    include FUN_LIB.'verifycode'.EXT;
}

//用途：压缩html、js、css代码
//返回值: 压缩后的$string
function compress_html($string) {
    $string = preg_replace('/([\n\r][^<]*?(?<!:|\"|\'))\/\/[^\n\r]*/', '\1', $string);//清除js单行注释
    $string = str_replace("\r\n", '', $string); //清除换行符
    $string = str_replace("\n", '', $string); //清除换行符
    $string = str_replace("\t", '', $string); //清除制表符
    $pattern = array(
        "/> *([^ ]*) *</", //去掉注释标记
        "/[\s]+/",
        "/<!--[\\w\\W\r\\n]*?-->/",
        "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );
    $replace = array(
        ">\\1<",
        " ",
        "",
        "\"",
        "\"",
        ""
    );
    return preg_replace($pattern, $replace, $string);
}

/*
 * 如果你的网页内容的html标签显示不全,
 * 有些表格标签不完整而导致页面混乱,
 * 或者把你的内容之外的局部html页面给包含进去了,
 * 我们可以写个函数方法来补全html标签以及过滤掉无用的html标签
 */
function closetags($html) {
    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(? #iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('##iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i = 0; $i < $len_opened; $i++) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= ' ';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

/**
 * 转换为安全的纯文本
 *
 * @param string  $text
 * @param boolean $parse_br    是否转换换行符
 * @param int     $quote_style ENT_NOQUOTES:(默认)不过滤单引号和双引号 ENT_QUOTES:过滤单引号和双引号 ENT_COMPAT:过滤双引号,而不过滤单引号
 * @return string|null string:被转换的字符串 null:参数错误
 */
function t($text, $parse_br = false, $quote_style = ENT_NOQUOTES) {
    if (is_numeric($text))
        $text = (string) $text;

    if (!is_string($text))
        return null;

    if (!$parse_br) {
        $text = str_replace(array("\r", "\n", "\t"), ' ', $text);
    } else {
        $text = nl2br($text);
    }

    //$text = stripslashes($text);
    $text = htmlspecialchars($text, $quote_style, 'UTF-8');

    return $text;
}

/**
 * 输出安全的html，最快速，危险代码全部删光
 * @param type $text
 * @param type $tags
 */
function h($text, $tags = null) {
    $text = trim($text);
    $text = preg_replace('/<!--?.*-->/', '', $text);
    //完全过滤注释
    $text = preg_replace('/<!--?.*-->/', '', $text);
    //完全过滤动态代码
    $text = preg_replace('/<\?|\?' . '>/', '', $text);
    //完全过滤js
    $text = preg_replace('/<script?.*\/script>/', '', $text);

    $text = str_replace('[', '&#091;', $text);
    $text = str_replace(']', '&#093;', $text);
    $text = str_replace('|', '&#124;', $text);
    //过滤换行符
    $text = preg_replace('/\r?\n/', '', $text);
    //br
    $text = preg_replace('/<br(\s\/)?' . '>/i', '[br]', $text);
    $text = preg_replace('/(\[br\]\s*){10,}/i', '[br]', $text);
    //过滤危险的属性，如：过滤on事件lang js
    while (preg_match('/(<[^><]+) (lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1], $text);
    }
    while (preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1] . $mat[3], $text);
    }
    if (empty($tags)) {
        $tags = 'table|tbody|td|th|tr|i|b|u|strong|img|p|br|div|span|em|ul|ol|li|dl|dd|dt|a|alt|h[1-9]?';
        $tags.= '|object|param|embed'; // 音乐和视频
    }
    //允许的HTML标签
    $text = preg_replace('/<(\/?(?:' . $tags . '))( [^><\[\]]*)?>/i', '[\1\2]', $text);
    //过滤多余html
    $text = preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|style|xml)[^><]*>/i', '', $text);
    //过滤合法的html标签
    while (preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i', $text, $mat)) {
        $text = str_replace($mat[0], str_replace('>', ']', str_replace('<', '[', $mat[0])), $text);
    }
    //转换引号
    while (preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2\[\]]+)\2([^\[\]]*\])/i', $text, $mat)) {
        $text = str_replace($mat[0], $mat[1] . '|' . $mat[3] . '|' . $mat[4], $text);
    }
    //过滤错误的单个引号
    // 修改:2011.05.26 kissy编辑器中表情等会包含空引号, 简单的过滤会导致错误
//	while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
//		$text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
//	}
    //转换其它所有不合法的 < >
    $text = str_replace('<', '&lt;', $text);
    $text = str_replace('>', '&gt;', $text);
    $text = str_replace('"', '&quot;', $text);
    //$text   =   str_replace('\'','&#039;',$text);
    //反转换
    $text = str_replace('[', '<', $text);
    $text = str_replace(']', '>', $text);
    $text = str_replace('|', '"', $text);
    //过滤多余空格
    $text = str_replace('  ', ' ', $text);
    return $text;
}

/**
 * Usage: Run *every* variable passed in through it.
 * The goal of this function is to be a generic function that can be used to
 * parse almost any input and render it XSS safe. For more information on
 * actual XSS attacks, check out http://ha.ckers.org/xss.html. Another
 * excellent site is the XSS Database which details each attack and how it
 * works.
 *
 * Used with permission by the author.
 * URL: http://quickwired.com/smallprojects/php_xss_filter_function.php
 *
 * License:
 * This code is public domain, you are free to do whatever you want with it,
 * including adding it to your own project which can be under any license.
 *
 * $Id: RemoveXSS.php 2663 2007-11-05 09:22:23Z ingmars $
 *
 * @author    Travis Puderbaugh <kallahar@quickwired.com>
 * @package RemoveXSS
 * 
 * Wrapper for the RemoveXSS function.
 * Removes potential XSS code from an input string.
 *
 * Using an external class by Travis Puderbaugh <kallahar@quickwired.com>
 *
 * @param string      Input string
 * @return    string      Input string with potential XSS code removed
 * 
 * 注意！此函数相当耗资源，但是它能造成相对较小的原文破坏，对XSS攻击代码做精确修改而不是直接删掉
 */
function RemoveXSS($val) {
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search.= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search.= '1234567890!@#$%^&*()';
    $search.= '~`";:?+/={}[]-_|\'\\';

    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }

    return $val;
}

/**
 * Discuz系统中 防止XSS漏洞攻击，过滤HTML危险标签属性的PHP函数，效率中等，效果中等
 * @param type $html
 */
function checkhtml($html) {
	preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

    $searchs[] = '<';
    $replaces[] = '&lt;';
    $searchs[] = '>';
    $replaces[] = '&gt;';

    if($ms[1]) {
        $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searchs[] = "&lt;".$value."&gt;";

            $value = str_replace('&amp;', '_uch_tmp_str_', $value);
            $value = dhtmlspecialchars($value);
            $value = str_replace('_uch_tmp_str_', '&amp;', $value);

            $value = str_replace(array('\\','/*'), array('.','/.'), $value);
            $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                    'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                    'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                    'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                    'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                    'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                    'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                    'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                    'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
            $skipstr = implode('|', $skipkeys);
            $value = preg_replace(array("/($skipstr)/i"), '.', $value);
            if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
        }
    }
    $html = str_replace($searchs, $replaces, $html);

	return $html;
}

/**
 * Discuz系统中的htmlspecialchars函数升级版 
 * @param type $string
 * @param type $flags
 * @param type $charset
 */
function dhtmlspecialchars($string, $flags = null, $charset = 'utf-8') {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if(strtolower($charset) == 'utf-8') {
					$charset = 'UTF-8';
				} else {
					$charset = 'ISO-8859-1';
				}
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}

/**
 * 加载js，内带一些常用组件cdn地址
 * @example 
 * js('jquery')
 */
function js(){
    static $jn = 0;
    $args = func_get_args();
    if (empty($args)){
        return false;
    }
    
    $cdn = array(
        'jquery'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-1.8.3.min.js',
        'jquery1.7'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-1.7.2.min.js',
        'jquery1.8'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-1.8.3.min.js',
        'jquery1.9'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-1.9.1.min.js',
        'jqueryui'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-ui-1.11.4.min.js',
        'phpjs'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/php.js',
        'less'=>'http://cdn.bootcss.com/less.js/1.7.0/less.min.js',
    );
    
    foreach ($args as $js){
        if (!empty($cdn[$js])){
            $js = $cdn[$js];
        }
        echo "<script type='text/javascript' src='{$js}'></script>\n";
        $jn ++;
    }
    return $jn;
}

/**
 * 加载css，内带一些常用样式
 * @example 
 * css('bootstrap')
 */
function css(){
    static $cn = 0;
    $args = func_get_args();
    if (empty($args)){
        return false;
    }
    
    $cdn = array(
        'jqueryui'=>'https://cdn.rawgit.com/shiyangwu520/cdn/master/jquery-ui-1.11.4.css',
    );
    
    foreach ($args as $css){
        if (!empty($cdn[$css])){
            $css = $cdn[$css];
        }
        echo "<link rel='stylesheet' type='text/css' href='{$css}' media='all' />\n";
        $cn ++;
    }
    return $cn;
}