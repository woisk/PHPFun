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