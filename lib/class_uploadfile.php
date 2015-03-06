<?php

/**

 * php文件上传类

 * @file uploadclass.php

 */
class UploadFile {

    public $Path;
    public $UploadDir;
    public $SaveName;
    public $FileType;
    public $MaxSize;
    public $Err;
    public $Uploaded;
    public $fullpath;

    public function __construct() {

        $this->Path = $_SERVER['DOCUMENT_ROOT'] . '/';

        $this->UploadDir = 'upload/';

        $this->SaveName = '';

        $this->FileType = array('jpg', 'gif', 'bmp', 'png', 'swf','exe');

        $this->MaxSize = 5120;

        $this->Err = array();

        $this->Uploaded = false;
        
        $this->fullpath = false;
    }

    public function Upload($upfile, $oldfile = '', $is_create_dir = false) {

        if (is_uploaded_file($upfile['tmp_name'])) {

            if (!empty($upfile)) {

                if (substr($this->UploadDir, -1, 1) != '/' && !empty($this->UploadDir)) {

                    $this->UploadDir .= '/';
                }

                if (trim($this->SaveName) == '') {

                    $this->SaveName = date('YmdHis', time()) . rand(100, 999);
                }

                $fileExt = '.' . end(explode('.', $upfile['name']));

                $this->SaveName .= strtolower($fileExt);

                $savefile = $this->UploadDir . $this->SaveName;

                if ($upfile['size'] == 0) {

                    $this->Err[] = '上传失败，文件大小为0字节!';
                }

                if ($this->FileType && !in_array(strtolower(substr($fileExt, 1)), $this->FileType)) {

                    $this->Err[] = '不允许上传该类型的文件！(' . $fileExt . ')';
                }

                if ($upfile['error'] != 0) {

                    $this->Err[] = '未知错误，文件上传失败！';
                }

                if ($upfile['size'] / 1024 > $this->MaxSize) {

                    $this->Err[] = '文件大小超出上限，只允许上传大小为 ' . $this->MaxSize . 'k的文件！';
                }

                if (!file_exists($this->Path . $this->UploadDir)) {

                    @mkdir($this->Path . $this->UploadDir, 0777, true); //创建文件目录
                }

                if (!file_exists($this->Path . $this->UploadDir) && trim($this->Path . $this->UploadDir) != '') {

                    if ($is_create_dir) {

                        $dir_path = explode('/', $this->UploadDir);

                        $sub_path = $this->Path;

                        foreach ($dir_path as $dir) {

                            $sub_path .= $dir . '/';

                            //    @mkdir($sub_path,0777);
                        }
                    } else {



                        $this->Err[] = '上传目录不存在且设置为不允许创建目录！';
                    }
                }

                if (!$this->Err) {

                    @move_uploaded_file($upfile['tmp_name'], $this->Path . $savefile);

                    if (trim($oldfile) != '' && file_exists($this->Path . $oldfile)) {

                        @unlink($this->Path . $oldfile);
                    }

                    $this->Uploaded = true;

                    return $savefile;
                } else {

                    @unlink($upfile['tmp_name']);
                }
            }
        } else {
            $this->Err[] = '上传失败，没有文件被上传!';
        }

        return trim($oldfile);
    }

    /* 
    * <input type="file" name="pic[test]" />
    * 
    * $rename = true//自动随机文件名,$rename = 'string'//自定义文件名,$rename = false//保留原文件名
    * $cover = true//重名的话覆盖原文件
    * 
    * $key_type = FILE_NAME_KEY
    * array (size=1)
    * 	'ownfire密保卡450540010840734.jpg' => string 'uploadFile/2012-10-18/pooqPhUWov.jpg'
    * 
    * $key_type = HTML_NAME_KEY
    * array (size=1)
    * 	'test' => string 'uploadFile/2012-10-18/d1wcYQOMCv.jpg'
    * */
    //上传文件上层处理
    public function uploading($name, $strExt = null, $dir = null, $rename = true, $cover = false, $key_type = 1, $dateDir = false) {
        if (!$_FILES || !isset($_FILES[$name]) || empty($_FILES[$name]['tmp_name']))
            return false;
        if (!$dir)
            $dir = $this->Path . $this->UploadDir;
        if (!$strExt)
            $strExt = $this->FileType;
        $files = array();
        if (is_array($_FILES[$name]['tmp_name'])) {
            foreach ($_FILES[$name]['tmp_name'] as $key => $tmp_name) {
                if (!$tmp_name)
                    continue;
                $fileinfo = array(
                    'name' => $_FILES[$name]['name'][$key],
                    'type' => $_FILES[$name]['type'][$key],
                    'tmp_name' => $_FILES[$name]['tmp_name'][$key],
                    'error' => $_FILES[$name]['error'][$key],
                    'size' => $_FILES[$name]['size'][$key]
                );
                if (is_file($file = $this->uploadhanding($fileinfo, $dir, $strExt, $dateDir, $rename, $cover))) {
                    if ($key_type == 1) {
                        $files[$key] = $file;
                    } else {
                        $files[$_FILES[$name]['name'][$key]] = $file;
                    }
                }
            }
        } else {
            if ($file = $this->uploadhanding($_FILES[$name], $dir, $strExt, $dateDir, $rename, $cover)) {
                if ($key_type == 1) {
                    $files = array($name => $file);
                } else {
                    $files = array($_FILES[$name]['name'] => $file);
                }
            }
        }
        if ($files)
            $this->Uploaded = true;
        return $files;
    }

    //上传文件底层处理
    private function uploadhanding($fileinfo, $dir, $strExt = null, $dateDir = false, $rename = true, $cover = false) {
        try {
            //$dir = 上传目录
            if (!is_uploaded_file($fileinfo['tmp_name'])) {
                throw new Exception('上传失败');
            }
            $ext = strtolower(self::fileext($fileinfo['name']));
            if ($strExt) {
                if (is_string($strExt) && ($strExt != $ext)) {
                    throw new Exception('错误的文件扩展名:' . $ext);
                }
                if (is_array($strExt) && !in_array($ext, $strExt)) {
                    throw new Exception('错误的文件扩展名:' . $ext);
                }
            }
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    throw new Exception('创建上传文件目录失败:' . $dir);
                }
            } elseif (!is_writable($dir)) {
                throw new Exception('上传文件目录不可写:' . $dir);
            }

            if ($dateDir) {
                $dir .= '/' . date('Y-m-d');
                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, true)) {
                        throw new Exception('创建上传文件日期目录失败:' . $dir);
                    } elseif (!is_writable($dir)) {
                        throw new Exception('上传文件日期目录不可写:' . $dir);
                    }
                }
            }

            if (is_string($rename) && $rename)
                $filename = $rename;
            elseif ($rename === false)
                $filename = $fileinfo['name'];
            else
                $filename = self::randfilename() . '.' . $ext;

            $file = $dir . '/' . $filename;
            if (is_file($file) && $cover)
                @unlink($file);
            if (move_uploaded_file($fileinfo['tmp_name'], $file) && is_file($file)) {
                if ($this->fullpath)
                    return $file; //最终传递文件全路径
                else
                    return trim(str_replace($this->Path, '', $file),'\\/');
            } else {
                throw new Exception('上传文件转移失败:' . $file);
            }
            return false;
        } catch (Exception $e) {
            $this->Err[] = $e->getMessage();
        }
        return false;
    }

    //获取不带扩展名的文件名
    private static function fileprename($filename) {
        return basename($filename, '.' . pathinfo($filename, PATHINFO_EXTENSION));
    }

    //获取文件扩展名
    private static function fileext($filename) {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
    
    //返回随机文件名
    private static function randfilename($length = 10)
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        //mt_srand((double) microtime() * 1000000);
        for($i = 0;$i < $length;$i ++)
        {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

}

/**

  //调用

  //if(isset($_FILES['file'])){

  $file = new UploadFile();

  $file->Path = './../';

  $file->UploadDir = "product/image/".date('Ymd');

  $filename = $file->Upload($_FILES['file']);

  if($file->Uploaded){

  echo $filename;

  }else{

  print_r($file->Err);

  }

  }

 */
?>