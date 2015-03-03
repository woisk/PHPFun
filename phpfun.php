<?php

/**
 * Description of phpfun
 *
 * @author wuxiao
 * @date 20140626
 */

defined('DS') or define('DS',DIRECTORY_SEPARATOR);
defined('EXT') or define('EXT','.php');
defined('FUN_ROOT') or define('FUN_ROOT',  realpath(dirname(__FILE__)).DS);
//错误信息自动显示
defined('FUN_ERROR_AUTO_RENDER') or define('FUN_ERROR_AUTO_RENDER',true);
//php debug开关
defined('FUN_DEBUG') or define('FUN_DEBUG',true);

defined('FUN_CACHE') or define('FUN_CACHE',  FUN_ROOT.'runtime'.DS.'cache'.DS);
defined('FUN_LOG') or define('FUN_LOG',  FUN_ROOT.'runtime'.DS.'log'.DS);

defined('FUN_LIB') or define('FUN_LIB',  FUN_ROOT.'lib'.DS);
defined('FUN_EXT') or define('FUN_EXT',  FUN_ROOT.'ext'.DS);
defined('FUN_CLASS') or define('FUN_CLASS',  FUN_ROOT.'class'.DS);

//date_default_timezone_set('PRC');

require_once FUN_ROOT.'init.php';;