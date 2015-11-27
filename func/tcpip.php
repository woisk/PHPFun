<?php

/**
 * 随机生成国内ip地址
 */
function rand_ip(){
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    $ip       = long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
    return $ip;
}

/**
 * 复杂版的socket交互函数
 */
function http_request(
    $verb = 'GET',             /* HTTP Request Method (GET and POST supported) */ 
    $ip,                       /* Target IP/Hostname */ 
    $port = 80,                /* Target TCP port */ 
    $uri = '/',                /* Target URI */ 
    $getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
    $custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */ 
    $timeout = 1,           /* Socket timeout in seconds */ 
    $req_hdr = false,          /* Include HTTP request headers */ 
    $res_hdr = false           /* Include HTTP response headers */ 
    )
{
    $ret = '';
    $verb = strtoupper($verb);
    
    if (is_string($getdata))
        parse_str($getdata, $getdata);
    if (is_string($postdata))
        parse_str($postdata, $postdata);
    if (is_string($cookie))
        parse_str($cookie, $cookie);
    
    $getdata_str = count($getdata) ? '?' : '';
    $postdata_str = '';
    $cookie_str = '';

    foreach ($getdata as $k => $v) 
                $getdata_str .= urlencode($k) .'='. urlencode($v) . '&'; 

    foreach ($postdata as $k => $v) 
        $postdata_str .= urlencode($k) .'='. urlencode($v) .'&'; 

    foreach ($cookie as $k => $v) 
        $cookie_str .= urlencode($k) .'='. urlencode($v) .'; '; 

    $crlf = "\r\n"; 
    $req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf; 
    $req .= 'Host: '. $ip . $crlf; 
    $req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf; 
    $req .= 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . $crlf; 
    $req .= 'Accept-Language: en-us,en;q=0.5' . $crlf; 
    $req .= 'Accept-Encoding: deflate' . $crlf; 
    $req .= 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7' . $crlf; 
    
    foreach ($custom_headers as $k => $v) 
        $req .= $k .': '. $v . $crlf; 
        
    if (!empty($cookie_str)) 
        $req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf; 
        
    if ($verb == 'POST' && !empty($postdata_str)) 
    { 
        $postdata_str = substr($postdata_str, 0, -1); 
        $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf; 
        $req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
        $req .= $postdata_str; 
    } 
    else $req .= $crlf; 
    
    if ($req_hdr) 
        $ret .= $req; 
    
    if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false) 
        return "Error $errno: $errstr\n"; 
    
    stream_set_timeout($fp, 0, $timeout * 1000); 
    
    fputs($fp, $req); 
    while ($line = fgets($fp)) $ret .= $line; 
    fclose($fp); 
    
    if (!$res_hdr) 
        $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4); 
    
    return $ret; 
}

/**
 * 简单版的socket交互函数
 */
function socket_request(
    $ip,                       /* Target IP/Hostname */ 
    $port = 80,                /* Target TCP port */ 
    $req = '',                /* Target URI */
    $timeout = 1           /* Socket timeout in seconds */ 
    )
{
    $ret = '';
    
    if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
        return "Error $errno: $errstr\n";
	//var_dump($fp);
    
    stream_set_timeout($fp, 0, $timeout * 1000);
    
    $r = fputs($fp, $req);
	//var_dump($r);
    while ($line = fgets($fp)) $ret .= $line;
    fclose($fp);
    
    return $ret;
}

/* 发送字节流数据
 * 
 * 接收端如何接收：
 * file_get_contents("php://input");
 */
function phpinput(
    $url,                       /* Target IP/Hostname */
    $data = '',
    $timeout = 1,           /* Socket timeout in seconds */
    $req_hdr = false,          /* Include HTTP request headers */ 
    $res_hdr = false           /* Include HTTP response headers */ 
    ) 
{
    if (!$url = parse_url($url))
        return 'Incorrect url';
    if (empty($url['host']) || empty($url['path']))
        return 'Incorrect url';

    $ret = '';
    $ip = $url['host'];
    $uri = $url['path'];
    $port = empty($url['port']) ? 80 : $url['port'];
    $getdata_str = empty($url['query']) ? '' : '?'.$url['query'];
    
    $crlf = "\r\n";
    $req = 'POST '. $uri . $getdata_str .' HTTP/1.1' . $crlf;
    $req .= 'Host: '. $ip . $crlf;
    //$req .= 'User-Agent: Mozilla/5.0 Firefox/3.6.12' . $crlf;
    $req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf;
    $req .= 'Content-Length: '.strlen($data) . $crlf;
    $req .= 'Connection: close' . $crlf.$crlf;
    $req .= $data.$crlf.$crlf;
    
    if ($req_hdr) 
        $ret .= $req; 
    
    if (($fp = @fsockopen($ip, $port, $errno, $errstr)) == false)
        return "Error $errno: $errstr\n";
    
    stream_set_timeout($fp, 0, $timeout * 1000);
    
    fputs($fp, $req);
    while ($line = fgets($fp)) $ret .= $line;
    fclose($fp);
    
    if (!$res_hdr) 
        $ret = substr($ret, strpos($ret, "\r\n\r\n") + 4);
    
    return $ret; 
}