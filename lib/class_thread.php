<?php

/**
 * php多进程类
 * @author wuxiao
 * @date 2015-10-27
 * @example 
 * 
    $a = 890;
    $t = new thread;
    $t->daemon(function() use ($a){
        echo $a;
        sleep(1);
    });
    $t->fork(function(){
        echo 456;
    });
    $t->run();
 */
class Thread{
    
    /**
     * 子进程pid池
     * @var type 
     */
    protected $pid = array();
    
    /*
     * 非守护进程运行时
     */
    private $fork = array();

    /**
     * 守护进程运行时
     * @var type 
     */
    private $daemon = array();
    
    /**
     * 正在监控的守护进程pid和运行时关联
     * @var type 
     */
    private $monitor = array();

    /**
     * 返回所有子进程pid
     * @return array
     */
    public function getpid(){
        return array_values($this->pid);
    }
    
    /**
     * 非守护进程加入
     * @param callable $child_thread 进程运行时内容
     * @param type $at_once 是否马上运行
     * @return boolean/pid
     */
    public function fork(callable $child_thread, $at_once = false){
        //如果不是有效的运行池，返回失败
        if (!is_callable($child_thread)){
            return false;
        }
        
        if (!$at_once){
            $this->fork[] = $child_thread;
        }else{
            $pid = pcntl_fork();
            if ($pid == -1) {
                //子进程创建失败
                //echo "\nfork failed!";
                return false;
            }else{
                if ($pid > 0){
                    //父进程
                    $this->pid[$pid] = $pid;
                    return $pid;
                }else{
                    //子进程
                    $child_thread();
                    exit(0);
                }
            }
        }
        return true;
    }
    
    /**
     * 守护进程加入
     * @param callable $child_thread 进程运行时内容
     * @param type $at_once 是否马上运行
     * @return boolean/pid
     */
    public function daemon(callable $child_thread, $at_once = false){
        //如果不是有效的运行池，返回失败
        if (!is_callable($child_thread)){
            return false;
        }
        
        if (!$at_once){
            $this->daemon[] = $child_thread;
        }else{
            $pid = pcntl_fork();
            if ($pid == -1) {
                //子进程创建失败
                //echo "\nfork failed!";
                return false;
            }else{
                if ($pid > 0){
                    //父进程
                    $this->pid[$pid] = $pid;
                    $this->monitor[$pid] = $child_thread;
                    return $pid;
                }else{
                    //子进程
                    $child_thread();
                    exit(0);
                }
            }
        }
        return 0;
    }
    
    /**
     * 开始运行
     */
    public function run(){
        foreach ($this->fork as $child_thread){
            $this->fork($child_thread,true);
        }
        
        foreach ($this->daemon as $child_thread){
            $this->daemon($child_thread,true);
        }
        
        //安装信号处理器
        $this->signalhandle();
        //开始监控子进程
        $this->monitor();
    }
    
    /**
     * 杀死所有子进程
     */
    private function killall(){
        foreach ($this->getpid() as $pid){
            @posix_kill($pid, SIGINT);
        }
        exit;
    }
    
    /**
     * 信号处理器
     */
    private function signalhandle(){
        pcntl_signal(SIGTERM, array($this,'killall'));//KILL命令的默认不带参数发送的信号就是SIGTERM
        pcntl_signal(SIGINT, array($this,'killall'));//ctrl+c
    }
    
    /**
     * 开始监控守护进程，中断则马上重启
     * @return type
     */
    private function monitor(){        
        do{
            usleep(500000);
            //立刻返回子进程状态，-1表示检查所有子进程
            $pid = pcntl_waitpid(-1,$status ,WNOHANG || WUNTRACED);
            //调用等待信号的处理器 
            pcntl_signal_dispatch();
        }while(($pid <= 0) || !isset($this->monitor[$pid]));
        
        if (!empty($this->monitor[$pid])){
            $child_thread = $this->monitor[$pid];
            $this->daemon($child_thread,true);
            unset($this->monitor[$pid]);
        }
        unset($this->pid[$pid]);
        
        $this->monitor();
    }
}