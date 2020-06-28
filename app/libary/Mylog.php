<?php
namespace app\libary;

/**
 *本子类的功能主要是改善系统自带log类的不完善功能
 *系统自带log是每条每条地写日志，并不能现实出每次请求的日志
 *本类先收集即将写入的日志信息。析构函数中主动往日志里一口气写 
 *@author Administrator
 */
class Mylog
{
    private $begin_time;
    
    /**将要写的日志
     * @var array
     */
    private $data;
    
    /**是否已经执行保存
     * @var bool
     */
    private $hasSave = false;
    
    /**保存的路径
     * @var string
     */
    private $save_dir;
    
    public  $moudle;

    /**
     * @param string $save_dir
     */
    public function setSave_dir($save_dir)
    {
        $this->save_dir = $save_dir;
    }

    /**
     * {@inheritDoc}
     * @see \Phalcon\Logger\Adapter\File::__construct()
     */
    public function __construct()
    {

        $this->begin_time = microtime(true);
        
        if (isset($_GET['_url']))
           $url = $_GET['_url'];
        else 
            $url = '';
        
       //mylog
        $this->setLog('-------------------------------['.date('Y-m-d H:i:s').']-------['.time().']------------------------------------',\Phalcon\Logger::DEBUG);
   
        
        
        $this->setLog('[url]'.$url.'   [ip]'.Util::getIp(),6);
        
        
        $this->setLog('[post]'.json_encode($_POST),\Phalcon\Logger::INFO);
       
        //默认保存地址 (没有抛错)
        $this->setSave_dir(BASE_DIR .'/runtime/log/'.date('Ymd').'_mylog.txt');
    }


    
    /** 设置将要写的日志
     * @author  WYY 2018年11月2日 下午4:53:25
     * @param string $message
     * @param int $type
     */
    public function setLog($message,$type)
    {
        $this->data[] = ['message'=>$message,'type'=>$type];
    }
    
    
    /** 保存
     * <li>也可以写在析构函数里面
     * <li>也可以保存到redis之类的。这里只是保存到file例子
     * @author  WYY 2018年11月2日 下午4:53:53
     */
    public function save() 
    {
          $use_time =( microtime(true) - $this->begin_time)*1000;
          
          if ($use_time > 10000)
          {
              $this->setLog('[total]'.(int)$use_time.'ms',\Phalcon\Logger::ALERT);
          }
          
          elseif ($use_time > 2000)
          {
              $this->setLog('[total]'.(int)$use_time.'ms',\Phalcon\Logger::WARNING);
          }
          
          else
          {
              $this->setLog('[total]'.(int)$use_time.'ms',\Phalcon\Logger::INFO);
          }
          
          
        //用file保存日志。也可以换成其它的适配器  
        //注意权限
        $log = new \Phalcon\Logger\Adapter\File($this->save_dir);

        
        $log->setFormatter(new \Phalcon\Logger\Formatter\Line('[%type%]%message%','Y-m-d H:i:s'));

        foreach ($this->data as $v)
        {
            $log->log($v['message'],$v['type']);
        }

    }
    
    
    /** 
     * #析构
     * @author  WYY 2020-06-19 10:08
     */
    public function __destruct() 
    {
        //自动保存
        if (Util::getConfig()->sys->save_log)
        {
            $this->save();
        }
    }
    
}

