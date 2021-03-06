<?php
//$_SESSION ['SafeCode'] = 验证码;
session_id() or session_start();
$type = 'gif';
$width = 40;
$height = 16;
header ( "Content-type: image/" . $type );
srand ( ( double ) microtime () * 1000000 );
$format = isset($format)?$format:'';
$randval = randStr ( 4, $format );
if ($type != 'gif' && function_exists ( 'imagecreatetruecolor' )) {
    $im = @imagecreatetruecolor ( $width, $height );
} else {
    $im = @imagecreate ( $width, $height );
}
$r = Array (225, 211, 255, 223 );
$g = Array (225, 236, 237, 215 );
$b = Array (225, 236, 166, 125 );

$key = rand ( 0, 3 );

$backColor = ImageColorAllocate ( $im, 255, 255, 255 ); //背景色（随机）
$borderColor = ImageColorAllocate ( $im, 255, 255, 255 ); //边框色
$pointColor = ImageColorAllocate ( $im, 192, 192, 192 ); //点颜色


@imagefilledrectangle ( $im, 0, 0, $width - 1, $height - 1, $backColor ); //背景位置
@imagerectangle ( $im, 0, 0, $width - 1, $height - 1, $borderColor ); //边框位置
$stringColor = ImageColorAllocate ( $im, 51, 51, 255 );//字体颜色

for($i = 0; $i <= 100; $i ++) {
    $pointX = rand ( 2, $width - 2 );
    $pointY = rand ( 2, $height - 2 );
    @imagesetpixel ( $im, $pointX, $pointY, $pointColor );
}

@imagestring ( $im, 3, 5, 1, $randval, $stringColor );
$ImageFun = 'Image' . $type;
$ImageFun ( $im );
@ImageDestroy ( $im );
$_SESSION ['SafeCode'] = $randval;
//产生随机字符串
function randStr($len = 6, $format = 'ALL') {
    switch (strtoupper($format)) {
        case 'ALL' :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
        case 'CHAR' :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case 'NUMBER' :
            $chars = '0123456789';
            break;
        default :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
    }
    $string = "";
    while ( strlen ( $string ) < $len )
        $string .= substr ( $chars, (mt_rand () % strlen ( $chars )), 1 );
    return $string;
}