<?php

//use Exception as BaseException;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/*class Exception extends BaseException
{
}*/

class whoops {
    
    public function __construct($pretty = true) {
        $run = new Run();
        if ($pretty){
            $handler = new PrettyPageHandler();
        }else{
            $handler = new JsonResponseHandler();
        }
        $run->pushHandler($handler);
        $run->register();
    }
}
