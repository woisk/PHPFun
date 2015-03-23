<?php

$GLOBALS['runtime'] = array();
$GLOBALS['auto_render'] = 'render';
$GLOBALS['unittest_string_length'] = 0;

function ut_start($tag = null,$render = null){
    global $runtime,$auto_render;
    ob_start();
    if ($tag === null)
        $tag = '#'.(count($runtime)+1);
    if (isset($runtime[$tag]))
        return error('tag <'.$tag.'> exists');
    
    $info = array(
        'render'=>('norender' === $render)?$render:$auto_render,
        'startMemory'=>memory_get_usage(),
        'startTime'=>microtime(true)
    );
    $runtime[$tag] = $info;
    return $runtime;
}

function ut_end($tag = null,$render = null){
    $endMemory = memory_get_usage();
    $endTime = microtime(true);
    $content = ob_get_contents();
    
    global $runtime,$startLength;
    if (empty($runtime))
        return error('there is no any unittest initialize');
    if ($tag === null){
        $keys = array_keys($runtime);
        $tag = ut_end($keys);
    }elseif (!isset($runtime[$tag]))
        return error('unittest tag <'.$tag.'> not exists');
    
    $result = &$runtime[$tag];
    if ('norender' === $render)
        $result['render'] = $render;
    list($result['endMemory'],$result['endTime'],$result['costMemory'],$result['costTime'],$result['contentLenght'])
            = array($endMemory,$endTime,$endMemory - $result['startMemory'],$endTime - $result['startTime'],strlen($content) - $GLOBALS['unittest_string_length']);
    
    $bt = debug_backtrace();
    $trace = array();
    foreach($bt as $k=>$v){
        list($class,$type,$function,$file,$line)
                = array(get($v,'class'),get($v,'type'),get($v,'function'),get($v,'file'),get($v,'line'));
        $trace[] = "#$k {$class}{$type}{$function}() called at [{$file}:{$line}]";
    }
    if ($result['render'] === 'render'){
        ob_start();
	echo '<div style="text-align:left">';
	pre(array(
            'Unit Test'=>'<span style="color:red">'.$tag.date(' Y-m-d H:i:s').'</span>',
            'Trace'=>$trace,
            'Cost Memory'=>'<span style="color:red">'.($result['costMemory']/1000).' KB</span>',
            'Cost Time'=>'<span style="color:red">'.$result['costTime'].' Second</span>',
            'Content Length'=>'<span style="color:red">'.$result['contentLenght'].' Bytes</span>'
	),'print_r');
	echo '</div>';
        $GLOBALS['unittest_string_length'] += strlen(ob_get_contents());
        ob_end_flush();
    }
    ob_end_flush();
    
    unset($runtime[$tag]);
}

/*
 * 启动whoops，适用于PHP环境的错误捕获与调试的PHP库
 */
function whoops($pretty = true){
    $whoops = new whoops($pretty);
}

/*
 * 返回错误等级/类型对应的PHP常量名
 */
function E_NAME($level){
    $E_NAME = array(
        1=>'E_ERROR',//致命的运行时错误。这类错误一般是不可恢复的情况，例如内存分配导致的问题。后果是导致脚本终止不再继续运行。 
        2=>'E_WARNING',//运行时警告 (非致命错误)。仅给出提示信息，但是脚本不会终止运行。 
        4=>'E_PARSE',//编译时语法解析错误。解析错误仅仅由分析器产生。 
        8=>'E_NOTICE',//运行时通知。表示脚本遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知。 
        16=>'E_CORE_ERROR',//在PHP初始化启动过程中发生的致命错误。该错误类似 E_ERROR ，但是是由PHP引擎核心产生的。
        32=>'E_CORE_WARNING',//PHP初始化启动过程中发生的警告 (非致命错误) 。类似 E_WARNING ，但是是由PHP引擎核心产生的。 
        64=>'E_COMPILE_ERROR',//致命编译时错误。类似 E_ERROR , 但是是由Zend脚本引擎产生的。 
        128=>'E_COMPILE_WARNING',//编译时警告 (非致命错误)。类似 E_WARNING ，但是是由Zend脚本引擎产生的。
        256=>'E_USER_ERROR',//用户产生的错误信息。类似 E_ERROR , 但是是由用户自己在代码中使用PHP函数 trigger_error() 来产生的。
        512=>'E_USER_WARNING',//用户产生的警告信息。类似 E_WARNING , 但是是由用户自己在代码中使用PHP函数 trigger_error() 来产生的。 
        1024=>'E_USER_NOTICE',//用户产生的通知信息。类似 E_NOTICE , 但是是由用户自己在代码中使用PHP函数 trigger_error() 来产生的。 
        2048=>'E_STRICT',//启用 PHP 对代码的修改建议，以确保代码具有最佳的互操作性和向前兼容性。 
        4096=>'E_RECOVERABLE_ERROR',//可被捕捉的致命错误。 它表示发生了一个可能非常危险的错误，但是还没有导致PHP引擎处于不稳定的状态。 如果该错误没有被用户自定义句柄捕获 (参见 set_error_handler() )，将成为一个 E_ERROR 　从而脚本会终止运行。 
        8192=>'E_DEPRECATED',//运行时通知。启用后将会对在未来版本中可能无法正常工作的代码给出警告。 
        16384=>'E_USER_DEPRECATED',//用户产少的警告信息。 类似 E_DEPRECATED , 但是是由用户自己在代码中使用PHP函数 trigger_error() 来产生的。 
        30719=>'E_ALL',//E_STRICT 出外的所有错误和警告信息。 
    );
    return isset($E_NAME[$level]) ? $E_NAME[$level] : null;
}

/*
 * 格式化输出
 */
function dump($o){
    $is_cmd = isset($_SERVER['argv']);
    if ($is_cmd){
        echo var_export($o,true);
    }else{
        echo '<pre>',var_export($o,true),"</pre>\n";
    }
}