<?php

function upload($name, $UploadDir = 'upload', $datedir = true) {
    if (empty($_FILES) || !isset($_FILES[$name]))
        return false;
    if ($_FILES[$name]['name'] != '') {
        $file = new UploadFile();
        $file->Path = FUN_ROOT;
        $file->UploadDir = $UploadDir;
        if ($datedir)
            $file->UploadDir .= date('/Ym');
        if (!is_dir(mk_dir($file->Path.$file->UploadDir)))
            return false;
        $filename = $file->uploading($name);

        if ($file->Uploaded) {
            //$filename = basename($filename);
            return $filename;
        } else {
            print_r($file->Err);
            return false;
        }
    }
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

//获得当前页面的域名，可以带上自定义uri
function domain($uri = '',$pre = 'http'){
    if (isset($_SERVER['SERVER_NAME']))
        $domain = $_SERVER['SERVER_NAME'];
    elseif (isset($_SERVER['HTTP_HOST']))
        $domain = $_SERVER['HTTP_HOST'];
    else
        $domain = '';
    
    return $pre.'://'.$domain.'/'.$uri;
}

//获得当前页面的完整url
function url(){
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST'];
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
    return $url;
}

//获取顶级域名
function topdomain($url) {
    $host = strtolower($url);
    if (strpos($host, '/') !== false) {
        $parse = @parse_url($host);
        $host = $parse ['host'];
    }
    $topleveldomaindb = array('com', 'edu', 'gov', 'int', 'mil', 'net', 'org', 'biz', 'info', 'pro', 'name', 'museum', 'coop', 'aero', 'xxx', 'idv', 'mobi', 'cc', 'me');
    $str = '';
    foreach ($topleveldomaindb as $v) {
        $str .= ($str ? '|' : '') . $v;
    }

    $matchstr = "[^\.]+\.(?:(" . $str . ")|\w{2}|((" . $str . ")\.\w{2}))$";
    if (preg_match("/" . $matchstr . "/ies", $host, $matchs)) {
        $domain = $matchs ['0'];
    } else {
        $domain = $host;
    }
    return $domain;
}

//返回$_POST
function post($key = null, $default = null) {
    $o = $_POST;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

//返回$_GET
function get($key = null, $default = null) {
    $o = $_GET;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

//返回$_REQUEST
function request($key = null, $default = null) {
    $o = $_REQUEST;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

//获取ip地址
function getip(){
    $onlineipmatches = array();
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    $onlineip = addslashes($onlineip);
    @preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $onlineip = isset($onlineipmatches[0])?$onlineipmatches[0]:'unknown';
    unset($onlineipmatches);
    return $onlineip;
}

//在线获取服务器端IP
function localip() {
    if (extension_loaded('curl')) {
        $ch = curl_init('http://www.ip138.com/ips138.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        
    }else{
        $output = file_get_contents(('http://www.ip138.com/ips138.asp'));
    }
    if (!isset($output) || !$output)
        return error(' accessing ip138 error');
    if (preg_match_all('/\[(.+)\]/', $output, $m)) {
        foreach ($m[1] as $output) {
            if (preg_match('/^[\d\.]+$/', $output, $s)) {
                return $s[0];
            }
        }
    }
    return error('[web\localip] cannot get any ip by preg match');
}

function curl($url, $post = null,$cookies = null,$headers = null) {
    if (!$url)
        return false;
    $init = curl_init();
    curl_setopt($init, CURLOPT_URL, $url);
    curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($init, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($init, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 (.NET CLR 3.5.30729)");
    //curl_setopt($init, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    if ($post) {
        curl_setopt($init, CURLOPT_POST, true);
        if (is_array($post) || is_string($post))
            curl_setopt($init, CURLOPT_POSTFIELDS, $post);
    }
    if ($cookies) {
        if (is_array($cookies))
            $cookies = http_build_cookie($cookies);
        if (is_string($cookies) && strlen($cookies))
            curl_setopt($init, CURLOPT_COOKIE, $cookies);
    }
    if ($headers) {
        if (is_array($headers)){
            foreach ($headers as $k => $v){
                if (is_string($k) && strlen($k)){
                    $headers[$k] = $k .': '. $v;
                }elseif(!strpos(':',$v))
                    $headers[$k] = '';
            }
        }elseif(!strpos(':',$headers))
            $headers = '';
        if ($headers = array_filter((array)$headers));
            curl_setopt($init, CURLOPT_HTTPHEADER, $headers);
    }
    $result = curl_exec($init);
    if ($error = curl_errno($init))
        return false;
    curl_close($init);
    return $result;
}

function curl_multi(array $urls) {
    if (empty($urls))
        return false;
    $init = curl_multi_init();
    $options = array(
        //启用时会将头文件的信息作为数据流输出
        CURLOPT_HEADER => 0,
        //文件流形式
        CURLOPT_RETURNTRANSFER => 1,
        //设置curl允许执行的最长秒数   
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
    );
    $conn = array();
    
    foreach ($urls as $k => $url) {
        $conn[$k] = curl_init($url);
        curl_setopt_array($conn[$k], $options);
        curl_multi_add_handle($init, $conn[$k]);
    }
    do{
        do {
            $mrc = curl_multi_exec($init, $running);
        } while (($mrc == CURLM_CALL_MULTI_PERFORM) || (curl_multi_select($init) != -1));
    }while ($running and $mrc == CURLM_OK);
    
    $ret = array();
    foreach ($urls as $k => $url) {
        $error = curl_error($conn[$k]);
        $ret[$k] = $error ? $error : curl_multi_getcontent($conn[$k]);
        curl_close($conn[$k]);
    }
    return  $ret;
}

//url是否存在
function url_exists($url)  
{  
   $parts = parse_url($url);  
   if (!$parts) {  
      return false; /* the URL was seriously wrong */  
   }  
  
   if (isset($parts['user'])) {  
      return false; /* user@gmail.com */  
   }  
  
   $ch = curl_init();  
   curl_setopt($ch, CURLOPT_URL, $url);  
  
   /* set the user agent - might help, doesn't hurt */  
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');  
   //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; wowTreebot/1.0; +http://wowtree.com)');  
   curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  
  
   /* try to follow redirects */  
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  
  
   /* timeout after the specified number of seconds. assuming that this script runs 
      on a server, 20 seconds should be plenty of time to verify a valid URL.  */  
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);  
   curl_setopt($ch, CURLOPT_TIMEOUT, 20);  
  
   /* don't download the page, just the header (much faster in this case) */  
   curl_setopt($ch, CURLOPT_NOBODY, true);  
   curl_setopt($ch, CURLOPT_HEADER, true);  
  
   /* handle HTTPS links */  
   if ($parts['scheme'] == 'https') {  
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  1);  
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
   }  
  
   $response = curl_exec($ch);  
   curl_close($ch);  
  
   /* allow content-type list */  
   $content_type = false;  
   if (preg_match('/Content-Type: (.+\/.+?)/i', $response, $matches)) {  
       switch ($matches[1])  
        {  
           case 'application/atom+xml':  
           case 'application/rdf+xml':  
           //case 'application/x-sh':  
           case 'application/xhtml+xml':  
           case 'application/xml':  
           case 'application/xml-dtd':  
           case 'application/xml-external-parsed-entity':  
           //case 'application/pdf':  
           //case 'application/x-shockwave-flash':  
              $content_type = true;  
              break;  
        }  
  
       if (!$content_type && (preg_match('/text\/.*/', $matches[1]) || preg_match('/image\/.*/', $matches[1]))) {  
           $content_type = true;  
        }  
   }  
  
   if (!$content_type) {  
      return false;  
   }  
  
   /*  get the status code from HTTP headers */  
   if (preg_match('/HTTP\/1\.\d+\s+(\d+)/', $response, $matches)) {  
      $code = intval($matches[1]);  
   } else {  
      return false;  
   }  
  
   /* see if code indicates success */  
   return (($code >= 200) && ($code < 400));  
}

function online_filesize($url) {
    if (function_exists('curl_init'))
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $results = explode("\n", trim(curl_exec($ch)));
        if (preg_match('/HTTP\/1\.\d+\s+(\d+)/', $results[0], $matches)) {  
            $code = intval($matches[1]);  
            if (($code < 200) || ($code >= 400))
                return null;
        }
        foreach($results as $line) {
                if (strtok($line, ':') == 'Content-Length') {
                        $parts = explode(":", $line);
                        return intval(trim($parts[1]));
                }
        }
    }else{
        $url = parse_url($url);
        if ($fp = @fsockopen($url['host'], empty($url['port']) ? 80 : $url['port'], $error)) {
            fputs($fp, "GET " . (empty($url['path']) ? '/' : $url['path']) . " HTTP/1.1\r\n");
            fputs($fp, "Host: {$url['host']}\r\n\r\n");
            while (!feof($fp)) {
                if ($tmp = trim(fgets($fp))){
                    if (preg_match('/HTTP\/1\.\d+\s+(\d+)/', $tmp, $matches)) {  
                        $code = intval($matches[1]);  
                        if (($code < 200) || ($code >= 400))
                            return null;
                    }
                    break;
                }
            }
            while (!feof($fp)) {
                $tmp = fgets($fp);
                if (trim($tmp) == '') {
                    break;
                } else if (preg_match('/Content-Length:(.*)/si', $tmp, $arr)) {
                    return intval(trim($arr[1]));
                }
            }
            return null;
        } else {
            return null;
        }
    }
}

function online_filetype($url) {
    if (function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_NOBODY, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $results = explode("\n", trim(curl_exec($ch)));
            foreach($results as $line) {
                    if (strtok($line, ':') == 'Content-Type') {
                            $parts = explode(":", $line);
                            return trim($parts[1]);
                    }
            }
        }else
        {
            $url = parse_url($url);
            if ($fp = @fsockopen($url['host'], empty($url['port']) ? 80 : $url['port'], $error)) {
                fputs($fp, "GET " . (empty($url['path']) ? '/' : $url['path']) . " HTTP/1.1\r\n");
                fputs($fp, "Host: {$url['host']}\r\n\r\n");
                while (!feof($fp)) {
                    $tmp = fgets($fp);
                    if (trim($tmp) == '') {
                        break;
                    } else if (preg_match('/Content-Type:(.*)/si', $tmp, $arr)) {
                        return trim((string)$arr[1]);
                    }
                }
                return null;
            } else {
                return null;
            }
        }
        return null;
}

function curl_upload($url, $file, $postFields = null, $fieldname = 'file') {
    if (!function_exists('curl_init'))
        return false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if (is_string($postFields)){
        parse_str($postFields,$postFields);
    }else{
        $postFields = (array)$postFields;
    }
    if (class_exists('\CURLFile')) {
        curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
        $postFields[$fieldname] = new \CURLFile(realpath($file));
        /*curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            $fieldname => new \CURLFile(realpath($file))
        ));*/
    } else {
        if (defined('CURLOPT_SAFE_UPLOAD')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }
        $filename = basename($file);
        $type = filemime($file);
        $postFields[$fieldname] = '@' . realpath($file) . ";type=" . $type . ";filename=" . $filename;
        /*curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            $fieldname => '@' . realpath($file) . ";type=" . $type . ";filename=" . $filename
        ));*/
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    
    $return_data = curl_exec($ch);
    curl_close($ch);
    return $return_data;
}

function file_get_contents_v2($path, $timeout = 120, $post = array()) {
    if (strpos($path, 'http') === 0) {
        $default_socket_timeout = ini_get('default_socket_timeout');
        ini_set('default_socket_timeout', $timeout);
        if (!empty($post)) {
            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => $timeout,
                    'method' => 'POST',
                    'content' => http_build_query($post, '', '&')
                )
            ));
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => "GET",
                    'timeout' => $timeout,
                )
            ));
        }
        /*$filesize = online_filesize($path);
        $content = file_get_contents($path, false, $context);
        if (!$content || (strlen($content) != $filesize)) {
            $content = file_get_contents($path, false, $context);
            if (!$content || (strlen($content) != $filesize)) {
                $content = false;
            }
        }*/
        $content = file_get_contents($path, false, $context);
        if (!$content) {
            $content = file_get_contents($path, false, $context);
        }
        ini_set('default_socket_timeout', $default_socket_timeout);
    } else {
        $content = file_get_contents($path);
    }
    return $content;
}
