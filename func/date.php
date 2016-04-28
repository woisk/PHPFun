<?php
/**
 * 时间日期
 * @author wuxiao
 */

/**
* 把时间转换成几分钟前、几小时前、几天前的文字
* @param int $time
* @return string
*/
function timeTran($time) {
    $t = time() - $time;
    $f = array(
        '31536000' => '年',
        '2592000' => '个月',
        '604800' => '星期',
        '86400' => '天',
        '3600' => '小时',
        '60' => '分钟',
        '1' => '秒'
    );
    foreach ($f as $k => $v) {
        if (0 != $c = floor($t / (int) $k)) {
            return $c . $v . '前';
        }
    }
 }