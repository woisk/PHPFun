<?php
/**
 * 图片相关
 * @author wuxiao
 */

/**
 * 从源图生成缩略图
 * @param string $srcFile 源文件
 * @param string $dstFile 目标文件
 * @param int $dstW 目标图片宽度
 * @param int $dstH 目标图片高度
 * @param boolean $deform 是否允许图片变形
 * @param int $rate 目标图片质量，满分100
 * @param string $markwords 水印文字
 * @param string $markimage 水印图片
 * @return string|boolean
 */
function makethumb($srcFile, $dstFile, $dstW = null, $dstH = null, $deform = null, $rate = 80, $markwords = null, $markimage = null) {
    if (is_null($dstW) && is_null($dstH)){
        return copy($srcFile,$dstFile);
    }
    $data = GetImageSize($srcFile);
    switch ($data[2]) {
        case 1:
            $im = @ImageCreateFromGIF($srcFile);
            break;
        case 2:
            $im = @ImageCreateFromJPEG($srcFile);
            break;
        case 3:
            $im = @ImageCreateFromPNG($srcFile);
            break;
    }
    if (!isset($im) || !$im)
        return False;
    $srcW = ImageSX($im);
    $srcH = ImageSY($im);
    $dstX = 0;
    $dstY = 0;
    $ni = ImageCreateTrueColor($dstW, $dstH);
    $white = ImageColorAllocate($ni, 255, 255, 255);
    $black = ImageColorAllocate($ni, 0, 0, 0);
    
    if ($deform){
        imagecopyresized($ni, $im, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);
    }else{
        if ($srcW * $dstH > $srcH * $dstW) {
            $fdstH = round($srcH * $dstW / $srcW);
            $dstY = floor(($dstH - $fdstH) / 2);
            $fdstW = $dstW;
        } else {
            $fdstW = round($srcW * $dstH / $srcH);
            $dstX = floor(($dstW - $fdstW) / 2);
            $fdstH = $dstH;
        }
        $dstX = ($dstX < 0) ? 0 : $dstX;
        $dstY = ($dstX < 0) ? 0 : $dstY;
        $dstX = ($dstX > ($dstW / 2)) ? floor($dstW / 2) : $dstX;
        $dstY = ($dstY > ($dstH / 2)) ? floor($dstH / s) : $dstY;
        imagefilledrectangle($ni, 0, 0, $dstW, $dstH, $white); // 填充背景色
        imagecopyresized($ni, $im, $dstX, $dstY, 0, 0, $fdstW, $fdstH, $srcW, $srcH);
    }
    if ($markwords != null) {
        if (!preg_match("/^([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}/", $markwords) == true
                || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){1}$/", $markwords) == true
                || preg_match("/([" . chr(228) . "-" . chr(233) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}[" . chr(128) . "-" . chr(191) . "]{1}){2,}/", $markwords) == true
            ) {
            $markwords = iconv("gb2312", "UTF-8", $markwords); //转换文字编码
        }
        $size = ($dstW+$dstH)/((800+600)/50);
        $angle = ($dstH/$dstW)/(600/800/30);
        $angle = ($angle>65)?65:$angle;
        $x = $dstW/strlen($markwords)/(800/10/450);
        $y = $dstH/(600/580);
        //imagettftext($ni, 50, 30, 450, 580, $black, "simhei.ttf", $markwords); //写入文字水印
        imagettftext($ni, $size, $angle, $x, $y, $black, "simhei.ttf", $markwords); //写入文字水印
    //参数依次为，文字大小|偏转度|横坐标|纵坐标|文字颜色|文字字体(不指定路径的话就在当前工作目录下找)|文字内容
    } elseif ($markimage != null) {
        $wimage_data = GetImageSize($markimage);
        switch ($wimage_data[2]) {
            case 1:
                $wimage = @ImageCreateFromGIF($markimage);
                break;
            case 2:
                $wimage = @ImageCreateFromJPEG($markimage);
                break;
            case 3:
                $wimage = @ImageCreateFromPNG($markimage);
                break;
        }
        $markimage_w = 88;$markimage_h = 31;
        $nwi = ImageCreateTrueColor($markimage_w, $markimage_h);
        imagecopyresized($nwi, $wimage, 0, 0, 0, 0, $markimage_w, $markimage_h, ImageSX($wimage), ImageSY($wimage));
        $x = $dstW/(800/500);
        $y = $dstH/(600/500);
        //imagecopy($ni, $wimage, 500, 560, 0, 0, 88, 31); //写入图片水印,水印图片大小默认为88*31
        imagecopy($ni, $nwi, $x, $y, 0, 0, $markimage_w, $markimage_h); //写入图片水印,水印图片大小默认为88*31
        imagedestroy($wimage);
    }
    switch ($data[2]) {
        case 1:
            imagegif($ni, $dstFile);
            break;
        case 2:
            imagejpeg($ni, $dstFile, $rate);
            break;
        case 3:
            $rate = round($rate / 10);
            imagepng($ni, $dstFile, ($rate >= 10) ? 9 : $rate);
            break;
    }
    imagedestroy($im);
    imagedestroy($ni);
    return is_file($dstFile) ? $dstFile : false;
}