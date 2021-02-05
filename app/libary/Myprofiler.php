<?php
namespace app\libary;

class Myprofiler extends \Phalcon\Db\Profiler
{
    public $mylog;
    
    
    /**在SQL语句将要发送给数据库前执行
     */
    public function beforeStartProfile(\Phalcon\Db\Profiler\Item $profile)
    {
        
    }
    
    /**在SQL语句已经被发送到数据库后执行
     */
    public function afterEndProfile(\Phalcon\Db\Profiler\Item $profile)
    {

        $log = Mydi::getLog();
        
        // 本次SQL执行时间 ms
        $cur_time = (int) ($profile->getTotalElapsedSeconds() * 1000);
        
        $str = '[sql][' . $cur_time . 'ms]' . $profile->getSQLStatement();
        
        if ($cur_time > 10000) {
            $log->log($str, \Phalcon\Logger::ALERT);
        }
        else if ($cur_time > 1000) {
            $log->log($str, \Phalcon\Logger::WARNING);
        }
        else if ($cur_time > 100) {
            $log->log($str, \Phalcon\Logger::NOTICE);
        } 
        else {
            $log->log($str, \Phalcon\Logger::INFO);
        }
                        
    }//end
    
    
}