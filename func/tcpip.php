<?php
/**
 * tcpip请求相关
 * @author wuxiao
 */

/**
 * 随机生成国内ip地址
 * @return string
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
 * @param enum $verb HTTP Request Method (GET and POST supported)
 * @param string $ip Target IP/Hostname
 * @param int $port Target TCP port
 * @param string $uri Target URI
 * @param array|string $getdata HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2')
 * @param array|string $postdata HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2')
 * @param array|string $cookie HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2')
 * @param array $custom_headers Custom HTTP headers ie. array('Referer: http://localhost/
 * @param int $timeout Socket timeout in seconds
 * @param boolean $req_hdr Include HTTP request headers
 * @param boolean $res_hdr Include HTTP response headers
 * @return string
 */
function http_request(
    $verb = 'GET',
    $ip,
    $port = 80,
    $uri = '/',
    $getdata = array(),
    $postdata = array(),
    $cookie = array(),
    $custom_headers = array(),
    $timeout = 1,
    $req_hdr = false,
    $res_hdr = false
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
 * @param string $ip Target IP/Hostname
 * @param int $port Target TCP port
 * @param string $req Target URI
 * @param int $timeout Socket timeout in seconds
 * @return string
 */
function socket_request(
    $ip,
    $port = 80,
    $req = '',
    $timeout = 1
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

/**
 * 发送字节流数据
 * 
 * 接收端如何接收：
 * 
 * file_get_contents("php://input");
 * @param string $url Target IP/Hostname
 * @param string $data Data to send
 * @param int $timeout Socket timeout in seconds
 * @param boolean $req_hdr Include HTTP request headers
 * @param boolean $res_hdr Include HTTP response headers
 * @return string
 */
function phpinput(
    $url,
    $data = '',
    $timeout = 1,
    $req_hdr = false,
    $res_hdr = false
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

/**
 * 创建udp服务器
 * @param string $ip 服务器ip
 * @param int $port 服务器端口
 * @param Closure $callback 接收请求回调函数，参数是udp请求内容
 */
function udp_server($ip,$port,Closure $callback){
    //消息结束符号
    $msg_eof = "\n\r\n\r";
    
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个SOCKET
    if ($socket)
        echo "socket_create() successed!\n";
    else
        echo "socket_create() failed:" . socket_strerror($socket) . "\n";
    
    $bind = socket_bind($socket, $ip, $port); // 绑定一个SOCKET
    if ($bind)
        echo "socket_bind() successed!\n";
    else
        echo "socket_bind() failed:" . socket_strerror($bind) . "\n";
    
    $listen = socket_listen($socket); // 间听SOCKET
    if ($listen)
        echo "socket_listen() successed!\n";
    else
        echo "socket_listen() failed:" . socket_strerror($listen) . "\n";

    
    do{
        $data = '';
        if (($msgsock = socket_accept($socket)) < 0) {
            echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
            break;
        }
        
        do {
            //接收客户端发来的信息
            $inMsg = socket_read($socket,1024);
            //服务端打印出相关信息
            echo "Receive : {$inMsg}";
            
            $data .= $inMsg;
            if ($eof_pos = strrpos($data, $msg_eof)){
                //执行回调
                $outMsg = $callback(substr($data, 0, $eof_pos));
                //给客户端发送信息
                $outMsg .= $eof_pos;
                socket_write($socket, $outMsg, strlen($outMsg));
                break;
            }
        } while ($inMsg !== false);
        
        socket_close($msgsock);
    }while(true);
    socket_close($socket);
}

/**
 * 发送udp请求
 * @param string $ip ip地址
 * @param int $port 端口
 * @param string $outMsg 发送的数据
 * @return string
 */
function udp_request($ip, $port, $outMsg = ''){
    //消息结束符号
    $msg_eof = "\n\r\n\r";
    
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个SOCKET
    if ($socket)
        echo "socket_create() successed!\n";
    else
        echo "socket_create() failed:" . socket_strerror($socket) . "\n";
    
    $conn = socket_connect($socket, $ip, $port); // 建立SOCKET的连接
    if ($conn)
        echo "Success to connection![" . $ip . ":" . $port . "]\n";
    else
        echo "socket_connect() failed:" . socket_strerror($conn) . "\n";
    
    $outMsg .= $msg_eof;
    socket_write($socket, $outMsg, strlen($outMsg));
    
    $data = '';
    do{
        $data .= socket_read($socket, 1024);
    }while (!$eof_pos = strrpos($data, $msg_eof));

    socket_close($socket);
    return $data;
}

/**
 * 创建udp服务器
 * @param string $ip 服务器ip
 * @param int $port 服务器端口
 * @param Closure $callback 接收请求回调函数，参数是udp请求内容
 */
function udp_stream_server($ip,$port,Closure $callback){
    //服务器信息
    $server = "udp://{$ip}:{$port}";
    //消息结束符号
    $msg_eof = "\n\r\n\r";
    $socket = stream_socket_server($server, $errno, $errstr, STREAM_SERVER_BIND);
    if (!$socket) {
        die("$errstr ($errno)");
    }

    $data = '';
    do {
        //接收客户端发来的信息
        $inMsg = stream_socket_recvfrom($socket, 1024, 0, $peer);
        //服务端打印出相关信息
        echo "Client : $peer\n";
        echo "Receive : {$inMsg}";
        //给客户端发送信息
        //$outMsg = substr($inMsg, 0, (strrpos($inMsg, $msg_eof))).' -- '.date("D M j H:i:s Y\r\n");
        
        $data .= $inMsg;
        if ($eof_pos = strrpos($data, $msg_eof)){
            //执行回调
            $outMsg = $callback(substr($data, 0, $eof_pos));
            //删除上一批数据
            $data = substr($data, $eof_pos+strlen($msg_eof));
            //给客户端发送信息
            stream_socket_sendto($socket, $outMsg, 0, $peer);
        }
    } while ($inMsg !== false);
}

/**
 * 发送udp请求
 * @param string $ip ip地址
 * @param int $port 端口
 * @param string $data 发送的数据
 * @return string
 */
function udp($ip, $port, $data = ''){
    $handle = stream_socket_client("udp://{$ip}:{$port}", $errno, $errstr);
    if( !$handle ){
        die("ERROR: {$errno} - {$errstr}\n");
    }
    //消息结束符号
    $msg_eof = "\n\r\n\r";
    fwrite($handle, $data.$msg_eof);
    $result = '';
    while ($line = fgets($handle)) $ret .= $line;
    fclose($handle);
    return $result;
}