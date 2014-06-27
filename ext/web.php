<?php

//弹出警报
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

function delay($url = '/',$timeout = 5,$msg = ''){
    if (intval($timeout) <= 0)
        return error('wrong timeout setting <'.$timeout.'>');
    if ($msg)
        echo "\r\n<br />",$msg;
    echo "<br />\r\n";
    echo '<span id="delaySecond">'.(int)$timeout.'</span>秒后跳转......';
    echo '<script>var delay='.(int)$timeout.';setInterval(function(){
        document.getElementById("delaySecond").innerText=--delay;
        if (delay<=0) location.href="'.$url.'";
    },1000)</script>';
}

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

//输出安全的html
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