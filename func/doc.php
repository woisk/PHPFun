<?php

/**
 * 根据数据生成excel文件
 * @param string $excel
 * @param array $data
 * @param string $writerType 文档类型，Excel2007(后缀xlsx)/Excel5(后缀xls)
 * @example excel('test.xlsx',array(array('id','name','createtime'),array(1,'wuxiao','2015-10-9 10:48')));
 * @example excel('test.xls',array(array('id','name','createtime'),array(1,'wuxiao','2015-10-9 10:48')),'Excel5');
 */
function excel($excel,array $data = null, $writerType = 'Excel2007'){
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $r = 1;
    foreach ($data as $row){
        $c = 'A';
        foreach ($row as $cell){
            $pCoordinate = "{$c}{$r}";
            //set value
            $objPHPExcel->getActiveSheet()->setCellValue($pCoordinate, $cell);
            //auto width
            $objPHPExcel->getActiveSheet()->getColumnDimension($c)->setAutoSize(true);
            //left align
            $objPHPExcel->getActiveSheet()->getStyle($pCoordinate)->applyFromArray(
                array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    )
                )
            );
            $c++;
        }
        $r++;
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $writerType);
    $objWriter->save($excel);
    return is_file($excel)?$excel:false;
}

/* 
 * 
 */
/**
 * 压缩文件为zip档案
 * @param string $zip
 * @param array $files
 * @return bool/string
 * @example
 * zip('压缩包文件名全名',array('要压缩的文件'=>'在压缩包中的新路径'));
 * zip('test.zip',array('jquery-1.11.1.min.js'=>'js/jquery'));
 * zip('test.zip',array('jquery-1.11.1.min.js','jquery-1.8.3.min.js'));
 * zip('test.zip','jquery-1.11.1.min.js');
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

/**
 * 备份文件
 * @param type $filename
 * @param type $cover
 * @return string
 */
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


/**
 * 发送邮件
 * @param type $to 发送到
 * @param type $subject 标题
 * @param type $mailbody 内容
 * @param array $smtp_server smtp服务器，格式：array('host'=>'服务器IP','port'=>'服务器端口','username'=>'用户名','password'=>'密码','fromname'=>'自定义发送者名称')
 * @param type $SMTPSecure 是否启用安全模式，ssl/tls
 * @return boolean
 * 
 * @example array(
 *  'host'=>'smtp.163.com',
 *  'username'=>'your password account like test@gmail.com',
 *  'password'=>'your email password',
 *  'port'=>25, //no ssl
 *  'fromname'=>'wuxiao'
 * )
 */
function email_send($to,$subject,$mailbody,array $smtp_server,$SMTPSecure = '') {
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