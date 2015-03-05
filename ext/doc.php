<?php

/* 
 * zip('test.xls',array());
 */
function excel($excel,array $files = null){
    require FUN_VENDOR . 'autoload.php';
}

/* 
 * zip('test.zip',array('cmnet'=>'newpathname'));
 */
function zip($zip,$files = null){
    $dir = realpath(dirname($zip));
    if (is_file($zip)){
        //echo "$zip exists,it will be overwritten\r\n<br />";
    }elseif (!is_dir($dir)){
        if (is_dir(mk_dir($dir)))
            die("$dir cannot create or unwritable");
    }
        
    $archive = new PclZip($zip);
    $errors = array();
    $v_list = $archive->create('');
    if ($v_list == 0){
        $errors[0] = $archive->errorInfo(true);
    }
    if ($files){
        $files = (array)$files;
        foreach ($files as $file_key=>$file_value){
            if (is_string($file_key) && strlen($file_key)){
                $in_zip_path = $file_value;
                $file = $file_key;
            }else{
                $in_zip_path = null;
                $file = $file_value;
            }
                
            //var_dump($file);
            if (file_exists($file)){
                //var_dump($file);
                $file = realpath($file);
                $file_path = dirname($file);
                //var_dump($file_path);
                if ($in_zip_path)
                    $v_list = $archive->add($file,PCLZIP_OPT_REMOVE_PATH,$file_path,PCLZIP_OPT_ADD_PATH,$in_zip_path);
                else
                    $v_list = $archive->add($file,PCLZIP_OPT_REMOVE_PATH,$file_path);
                if ($v_list == 0){
                    $errors[$file] = $archive->errorInfo(true);
                }
            }
        }
    }
    
    if ($errors && FUN_DEBUG){
        echo implode("\r\n<br />", $errors)."\r\n<br />";
    }
    
    return is_file($zip)?$zip:false;
}

function backup($filename,$cover = null){
    if (!is_file($filename))
        return error('source file '.$filename.' missed');
    $date = date('Ymd');
    $dir = realpath(dirname($filename));
    if (!is_writable($dir))
        return error('directory '.$dir.' is unwritable');
    $basename = fileprename($filename);
    $ext = fileext($filename);
    $dest = $dir.DS.$basename.'_'.$date.'.'.$ext;
    if (is_file($dest))
        if ($cover === 'cover')
            del($dest);
        else
            return error('dest file '.$dest.' exists');
    if (!copy($filename,$dest)){
            return copy($filename,$dest);
    }
    return $dest;
}

/*
 * @param array $smtp_server
 * array(
 *  'host'=>'smtp.163.com',
 *  'username'=>'your password account like test@gmail.com',
 *  'password'=>'your email password',
 *  'port'=>25, //no ssl
 *  'fromname'=>'wuxiao'
 * )
 */
//发邮件
function email_send($to,$subject,$mailbody,array $smtp_server,$SMTPSecure = '') {
    require FUN_VENDOR . 'autoload.php';
    
    $mail = new PHPMailer(); //建立邮件发送类
    $mail->CharSet = "utf-8";
    $mail->IsSMTP(); // 使用SMTP方式发送
    $mail->SMTPSecure = $SMTPSecure;//ssl/tls
    $mail->SMTPAuth = true; // 启用SMTP验证功能
    $mail->Host = $smtp_server['host']; // 您的企业邮局域名
    $mail->Username = $smtp_server['username']; // 邮局用户名(请填写完整的email地址)
    $mail->Password = $smtp_server['password']; // 邮局密码
    $mail->Port = $smtp_server['port'];
    $mail->From = $smtp_server['username']; //邮件发送者email地址
    $mail->FromName = isset($smtp_server['fromname'])?$smtp_server['fromname']:$smtp_server['username'];

    $mail->AddAddress("$to", "$to"); //收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
    //$mail->AddReplyTo("", "");
    //$mail->AddAttachment("/var/tmp/file.tar.gz"); // 添加附件
    $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
    $mail->Subject = $subject; //邮件标题
    $mail->Body = $mailbody; //邮件内容
    $mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //附加信息，可以省略

    if (!$mail->Send()) {
        echo "邮件发送失败. <p>";
        echo "错误原因: " . $mail->ErrorInfo;
        exit;
    }else{
        return true;
    }
}