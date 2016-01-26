<?php

/**
 * 是否微信访问
 * @return boolean
 */
function im_weixin(){
    return (bool)strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger');
}

/**
*
*手机移动设备识别函数
*
**/
function im_mobile() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_browser = Array(
        "mqqbrowser", //手机QQ浏览器
        "opera mobi", //手机opera
        "juc","iuc",//uc浏览器
        "fennec","ios","applewebKit/420","applewebkit/525","applewebkit/532","ipad","iphone","ipaq","ipod",
        "iemobile", "windows ce",//windows phone
        "240x320","480x640","acer","android","anywhereyougo.com","asus","audio","blackberry",
        "blazer","coolpad" ,"dopod", "etouch", "hitachi","htc","huawei", "jbrowser", "lenovo",
        "lg","lg-","lge-","lge", "mobi","moto","nokia","phone","samsung","sony",
        "symbian","tablet","tianyu","wap","xda","xde","zte",
        "ericsson","mot","sgh","sharp","sie-","philips","panasonic","alcatel","meizu","netfront",
        "ucweb","windowsce","palm","operamini","operamobi","openwave","nexusone","cldc","midp","mobile",
    );
    $is_mobile = false;
    foreach ($mobile_browser as $device) {
        if (stristr($user_agent, $device)) {
            $is_mobile = true;
            break;
        }
    }
    return $is_mobile;
}

/**
 * 处理$_FILES上传的文件，返回文件本地路径
 * @param type $name
 * @param type $UploadDir
 * @param type $datedir
 * @return boolean
 */
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

/**
 * 获得当前页面的域名，可以带上自定义uri
 * @param type $uri
 * @param type $pre
 * @return type
 */
function domain($uri = '',$pre = 'http'){
    if (isset($_SERVER['SERVER_NAME']))
        $domain = $_SERVER['SERVER_NAME'];
    elseif (isset($_SERVER['HTTP_HOST']))
        $domain = $_SERVER['HTTP_HOST'];
    else
        $domain = '';
    
    return $pre.'://'.$domain.'/'.$uri;
}

/**
 * 获得当前页面的完整url
 * @return type
 */
function url(){
    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    $url .= isset($_SERVER['SERVER_NAME'])?$_SERVER['SERVER_NAME']:$_SERVER['HTTP_HOST'];
    $url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : urlencode($_SERVER['PHP_SELF']) . '?' . urlencode($_SERVER['QUERY_STRING']);
    return $url;
}

/**
 * 获取url字符串中的顶级域名部分
 * @param type $url
 * @return type
 */
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

/**
 * 返回$_POST内容
 * @param type $key
 * @param type $default
 * @return type
 */
function post($key = null, $default = null) {
    $o = $_POST;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

/**
 * 返回$_GET内容
 * @param type $key
 * @param type $default
 * @return type
 */
function get($key = null, $default = null) {
    $o = $_GET;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

/**
 * 返回$_REQUEST内容
 * @param type $key
 * @param type $default
 * @return type
 */
function request($key = null, $default = null) {
    $o = $_REQUEST;
    if (!empty($o))
        array_walk_recursive($o, 'DOkillinject');
    if ($key)
        return isset($o[$key]) ? $o[$key] : $default;
    return $o;
}

/**
 * 获取ip地址
 * @return type
 */
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

/**
 * 在线获取服务器端IP
 * @return type
 */
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

/**
 * CURL
 * @param type $url
 * @param type $post
 * @param type $cookies
 * @param type $headers
 * @return boolean
 */
function curl($url, $post = null, $timeout = 120, $cookies = null, $headers = null, $user_agent = null, $referer = null) {
    if (!$url)
        return false;
    $init = curl_init();
    curl_setopt($init, CURLOPT_URL, $url);
    curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($init, CURLOPT_CONNECTTIMEOUT , $timeout);
    curl_setopt($init, CURLOPT_TIMEOUT , $timeout);
    curl_setopt($init, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($init, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($init, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.2.2) Gecko/20100316 Firefox/3.6.2 (.NET CLR 3.5.30729)");
    curl_setopt($init, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
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
    if (is_string($user_agent)){
        curl_setopt($init, CURLOPT_USERAGENT, $user_agent);
    }
    if (is_string($referer)){
        curl_setopt($init, CURLOPT_REFERER, $referer);
    }
    $result = curl_exec($init);
    if ($error = curl_errno($init))
        return false;
    curl_close($init);
    return $result;
}

/**
 * CURL多并发
 * @param array $urls 要请求的url地址数组
 * @param array $options curl请求选项
 * @example 
 * array(
        //post数据
        'post'=>array(
            'name'=>'abc',
            'get'=>true
        ),
        //超时时间，秒
        'timeout'=>120,
        //cookie数据
        'cookies'=>'asos=userCountryIso=CN&topcatid=1000&currencyid=2&currencylabel=USD',
        //header数据
        '$headers'=>array(
            'X-FORWARDED-FOR'=>'127.0.0.1',
            'CLIENT-IP'=>'127.0.0.1'
        ),
        //useragent信息
        'user_agent'=>'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:16.0) Gecko/20121026 Firefox/16.0',
        //请求来源信息
        'referer'=>'http://127.0.0.1/',
        CURLOPT_FOLLOWLOCATION=>0
    );
 * @example2
 * array(
        array(
            'post'=>'asos=userCountryIso=CN&topcatid=1000&currencyid=2&currencylabel=USD',
            'timeout'=>5,
        ),
        array(
            CURLOPT_TIMEOUT=>5,
            CURLOPT_TIMEOUT=>30,
            'cookies'=>'asos=userCountryIso=CN&topcatid=1000&currencyid=2&currencylabel=USD',
            'headers'=>'X-FORWARDED-FOR:127.0.0.1',
        ),
        array(
            CURLOPT_HEADER=>1,
            'user_agent'=>'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:16.0) Gecko/20121026 Firefox/16.0',
            'referer'=>'http://127.0.0.1/'
        )
    );
 * 
 * @return array
 */
function curl_multi(array $urls, array $options = null) {
    if (empty($urls))
        return false;
    $init = curl_multi_init();
    $default_option = array(
        //启用时会将头文件的信息作为数据流输出
        CURLOPT_HEADER => 0,
        //文件流形式
        CURLOPT_RETURNTRANSFER => 1,
        //设置curl允许执行的最长秒数   
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        //SSL安全验证
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        //自动跳转
        CURLOPT_FOLLOWLOCATION => 1
    );
    
    $options = (array)$options;
    if (array_keys($options) !== range(0,count($options)-1)){
        $options = array_fill(0,count($urls),$options);
    }
    
    foreach ($options as $i=>$option){
        
        if (isset($option['timeout'])) {
            $timeout = $option['timeout'];
            if ($timeout){
                $option[CURLOPT_TIMEOUT] = $timeout;
                $option[CURLOPT_CONNECTTIMEOUT] = $timeout;
            }
            unset($option['timeout']);
        }
        if (isset($option['post'])) {
            $post = $option['post'];
            if ($post){
                $option[CURLOPT_POST] = true;
                if (is_array($post) || is_string($post))
                    $option[CURLOPT_POSTFIELDS] = $post;
            }
            unset($option['post']);
        }
        if (isset($option['cookies'])) {
            $cookies = $option['cookies'];
            if ($cookies){
                if (is_array($cookies))
                    $cookies = http_build_cookie($cookies);
                if (is_string($cookies) && strlen($cookies))
                    $option[CURLOPT_COOKIE] = $cookies;
            }
            unset($option['cookies']);
        }
        if (isset($option['headers'])) {
            $headers = $option['headers'];
            if ($headers){
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
                    $option[CURLOPT_HTTPHEADER] = $headers;
            }
            unset($option['headers']);
        }
        if (isset($option['user_agent'])) {
            $user_agent = $option['user_agent'];
            if (is_string($user_agent)){
                $option[CURLOPT_USERAGENT] = $user_agent;
            }
            unset($option['user_agent']);
        }
        if (isset($option['referer'])) {
            $referer = $option['referer'];
            if (is_string($referer)){
                $option[CURLOPT_REFERER] = $referer;
            }
            unset($option['referer']);
        }
        
        $options[$i] = $option;
    }
    
    $conn = array();
    foreach ($urls as $k => $url) {
        $ch = curl_init($url);
        $option = !empty($options[$k]) ? ($options[$k] + $default_option) : $default_option;
        curl_setopt_array($ch, $option);
        curl_multi_add_handle($init, $ch);
        $conn[$k] = $ch;
    }
    
    do{
        do {
            $mrc = curl_multi_exec($init, $running);
            usleep(10000);
        } while (($mrc == CURLM_CALL_MULTI_PERFORM) || (curl_multi_select($init) != -1));
    }while ($running and $mrc == CURLM_OK);
    
    $ret = array();
    foreach ($urls as $k => $url) {
        $ch = $conn[$k];
        $error = curl_error($ch);
        $ret[$k] = $error ? false : curl_multi_getcontent($ch);
        curl_multi_remove_handle($init, $ch);
        curl_close($ch);
    }
    return  $ret;
}

/**
 * 检查url资源是否存在
 * @param type $url
 * @return boolean
 */
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

/**
 * 获得线上文件的大小
 * @param type $url
 * @return null
 */
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

/**
 * 获得线上文件的类型
 * @param type $url
 * @return null
 */
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

/**
 * 通过CURL向指定的url路径上传文件
 * @param type $url
 * @param type $file
 * @param type $postFields
 * @param type $fieldname
 * @return boolean
 */
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

/**
 * file_get_contents改进版
 * @param str $path
 * @param int $timeout
 * @param array $post
 * @param array $headers
 * @return type
 */
function file_get_contents_v2($path, $timeout = 120, $post = null, $headers = null, $user_agent = null, $referer = null) {
    if (is_file($path) || is_link($path) || is_dir($path)){
        return $content = file_get_contents($path);;
    }
    
    $default_socket_timeout = ini_get('default_socket_timeout');
    ini_set('default_socket_timeout', $timeout);
    $options = array(
        'http' => array(
            'method' => "GET",
            'timeout' => $timeout,
        )
    );
    if ($post) {
        if (is_array($post))
            $post = http_build_query($post, '', '&');
        $options['method'] = 'POST';
        $options['content'] = $post;
    }
    if (is_string($referer)) {
        $headers['Referer'] = $referer;
    }
    if ($headers) {
        $headers = (array)$headers;
        $options['header'] = '';
        foreach ($headers as $field=>$value){
            if (is_string($field)){
                $header = "{$field}: {$value}\r\n";
            }else{
                $header = "{$value}\r\n";
            }
            $options['header'] .= $header;
        }
    }
    if (is_string($user_agent)) {
        $options['user_agent'] = $user_agent;
    }
    $context = stream_context_create($options);
    $content = file_get_contents($path, false, $context);
    if (!$content) {
        $content = file_get_contents($path, false, $context);
    }
    ini_set('default_socket_timeout', $default_socket_timeout);
    
    return $content;
}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}