<?php

//创建文件夹
function mk_dir($dir) {
    if (is_dir($dir))
        return $dir;        //目录已经存在直接返回目录
    if (!mkdir($dir, 0777, true))
        return false;       //目录创建不成功就返回false
    if (is_dir($dir)) {  //如果目录已存在
        if ($fp = @fopen("$dir/test.test", 'w')) {    //创建一个名为test.test的文件来测试
            @fclose($fp);             //关闭文件流            
            @unlink("$dir/test.test");    //删除测试文件
            return $dir;            //能创建文件则说明可读取，返回值为 目录
        } else {
            return 1;          //不能创建文件，即不可写,返回值为 1
        }
    }
}

function ls($pattern = '*'){
    return glob($pattern);
}

function find($pattern,$dir = __ROOT__){
    $dir = trim($dir,'/\\').DS;
    if (!is_dir($dir))
        return error('dir '.$dir.' not exists');
    $pattern = '*'.trim($pattern,'*').'*';
    
    $matches = glob($dir.$pattern);
    $children_dir = glob($dir.'*',GLOB_ONLYDIR);
    foreach ($children_dir as $children_dir){
        $matches = array_merge($matches,find($pattern,$children_dir));
    }
    return $matches;
}

function finddir($pattern,$dir = __ROOT__){
    $dir = trim($dir,'/\\').DS;
    if (!is_dir($dir))
        return error('dir '.$dir.' not exists');
    $pattern = '*'.trim($pattern,'*').'*';
    
    $matches = glob($dir.$pattern);
    $children_dir = glob($dir.'*',GLOB_ONLYDIR);
    $matches = array_intersect($matches,$children_dir);
    foreach ($children_dir as $children_dir){
        $matches = array_merge($matches,finddir($pattern,$children_dir));
    }
    return $matches;
}

function findfile($pattern,$dir = __ROOT__){
    $dir = trim($dir,'/\\').DS;
    if (!is_dir($dir))
        return error('dir '.$dir.' not exists');
    $pattern = '*'.trim($pattern,'*').'*';
    
    $matches = glob($dir.$pattern);
    $children_dir = glob($dir.'*',GLOB_ONLYDIR);
    $matches = array_diff($matches,$children_dir);
    foreach ($children_dir as $children_dir){
        $matches = array_merge($matches,findfile($pattern,$children_dir));
    }
    return $matches;
}