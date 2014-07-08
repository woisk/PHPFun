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

function domain($uri = '',$pre = 'http'){
    if (isset($_SERVER['SERVER_NAME']))
        $domain = $_SERVER['SERVER_NAME'];
    elseif (isset($_SERVER['HTTP_HOST']))
        $domain = $_SERVER['HTTP_HOST'];
    else
        $domain = '';
    
    return $pre.'://'.$domain.'/'.$uri;
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
        if (is_array($post) && is_string($post))
            curl_setopt($init, CURLOPT_POSTFIELDS, $post);
    }
    var_dump($post,$cookies,$headers);
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
   //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');  
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; wowTreebot/1.0; +http://wowtree.com)');  
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
    $url = parse_url($url);
    if ($fp = @fsockopen($url['host'], empty($url['port']) ? 80 : $url['port'], $error)) {
        fputs($fp, "GET " . (empty($url['path']) ? '/' : $url['path']) . " HTTP/1.1\r\n");
        fputs($fp, "Host: {$url['host']}\r\n\r\n");
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