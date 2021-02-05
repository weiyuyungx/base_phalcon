<?php
namespace app\libary;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

/**
 * #DI容器
 * @author WYY 2021-02-04 17:41
 */
class Mydi extends FactoryDefault
{
    
    
    /** 
     * #构造
     * @author  WYY 2021-02-05 11:16
     */
    public function __construct() 
    {
        parent::__construct();
        
        //父类默认注入的服务组件
//         let this->_services = [
//             "router":             new Service("router", "Phalcon\\Mvc\\Router", true),
//             "dispatcher":         new Service("dispatcher", "Phalcon\\Mvc\\Dispatcher", true),
//             "url":                new Service("url", "Phalcon\\Mvc\\Url", true),
//             "modelsManager":      new Service("modelsManager", "Phalcon\\Mvc\\Model\\Manager", true),
//             "modelsMetadata":     new Service("modelsMetadata", "Phalcon\\Mvc\\Model\\MetaData\\Memory", true),
//             "response":           new Service("response", "Phalcon\\Http\\Response", true),
//             "cookies":            new Service("cookies", "Phalcon\\Http\\Response\\Cookies", true),
//             "request":            new Service("request", "Phalcon\\Http\\Request", true),
//             "filter":             new Service("filter", "Phalcon\\Filter", true),
//             "escaper":            new Service("escaper", "Phalcon\\Escaper", true),
//             "security":           new Service("security", "Phalcon\\Security", true),
//             "crypt":              new Service("crypt", "Phalcon\\Crypt", true),
//             "annotations":        new Service("annotations", "Phalcon\\Annotations\\Adapter\\Memory", true),
//             "flash":              new Service("flash", "Phalcon\\Flash\\Direct", true),
//             "flashSession":       new Service("flashSession", "Phalcon\\Flash\\Session", true),
//             "tag":                new Service("tag", "Phalcon\\Tag", true),
//             "session":            new Service("session", "Phalcon\\Session\\Adapter\\Files", true),
//             "sessionBag":         new Service("sessionBag", "Phalcon\\Session\\Bag"),
//             "eventsManager":      new Service("eventsManager", "Phalcon\\Events\\Manager", true),
//             "transactionManager": new Service("transactionManager", "Phalcon\\Mvc\\Model\\Transaction\\Manager", true),
//             "assets":             new Service("assets", "Phalcon\\Assets\\Manager", true)
//         ];
        
        $this->init(); //注入大量服务组件
    }
    


    /**
     * #注入常用服务
     * @author WYY 2021-02-05 09:44
     */
    private function init()
    {

        // 设置路由
        $this->setShared('router', function () {

            $router = new \Phalcon\Mvc\Router(false);

            $router->removeExtraSlashes(true);

            // //默认无参
            $router->add('/', array(
                'controller' => 'Index',
                'action' => 'index',
                'namespace' => 'app\controller'
            ));

            // 普通
            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action' => 2,
                'params' => 3
            ));

            // 用户权限
            $router->add('/user.([a-zA-Z]+)/:action/:params', array(
                'controller' => 1,
                'action' => 2,
                'params' => 3,
                'namespace' => 'app\controller\user'
            ));

            // 插件（测试用）
            $router->add('/Plugin.([a-zA-Z]+)/([a-zA-Z]+)/:action/:params', array(
                'controller' => 'Plugin',
                'action' => 'index',
                'PluginName' => 1,
                'PluginController' => 2,
                'PluginAction' => 3,
                'params' => 4,
                'namespace' => 'app\controller'
            ));

            // 不符合路由时
            $router->notFound([
                'controller' => 'Error',
                'action' => 'notFound',
                'namespace' => 'app\base'
            ]);

            return $router;
        });

        // session
        $this->setShared('session', function () {
            $session = new \Phalcon\Session\Adapter\Files([
                'uniqueId' => 'base_phalcon'
            ]);

            $session->start();

            return $session;
        });

        // 事件管理器
        $this->setShared('eventsManager', function () {
            $eventsManager = new \Phalcon\Events\Manager();
            
            //监听dispatch
            $eventsManager->attach('dispatch', function ($event, $dispatcher, $exception) {
                // 控制器执行抛错时
                if ($event->getType() == 'beforeException') {
                    $dispatcher->forward([
                        'controller' => 'Error',
                        'action' => 'exception',
                        'namespace' => 'app\base',
                        "params" => array(
                            'exception' => $exception
                        )
                    ]);
                }

                // 分发器找不到对应的controller/action
                if ($event->getType() == 'beforeNotFoundAction') {
                    $dispatcher->forward([
                        'controller' => 'Error',
                        'action' => 'notFound2',
                        'namespace' => 'app\base'
                    ]);
                }
            });
            
            
            $profiler = new Myprofiler();
            // 监听数据库的事件
            $eventsManager->attach('db', function ($event, $connection) use ($profiler) {
                if ($event->getType() == 'beforeQuery') {
                    // 操作前启动分析
                    $profiler->startProfile($connection->getSQLStatement());
                }
                if ($event->getType() == 'afterQuery') {
                    // 操作后停止分析
                    $profiler->stopProfile();
                }
            });

            return $eventsManager;
        });

        
        
        // 分发器
        $this->setShared("dispatcher", function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('app\controller');

            $dispatcher->setEventsManager(self::getEventsManager());

            return $dispatcher;
        });

        // 视图
        $this->setShared("view", function (){
            
            $di = self::getDi();
            $view = new View();
            $view->setViewsDir(BASE_DIR . "/app/view/");

            $view->registerEngines(array(
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
                '.html' => function ($view, $di) {
                $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $volt->setOptions(array(
                        // 模板是否实时编译
                        'compileAlways' => false,
                        // 模板编译目录
                        'compiledPath' => BASE_DIR . "/runtime/compiled/"
                    ));

                    return $volt;
                },

                '.tpl' => function ($view, $di) // 渲染生成model/dao/service文件时用
                {
                    $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                    $volt->setOptions(array(
                        // 模板是否实时编译
                        'compileAlways' => false,
                        // 模板编译目录
                        'compiledPath' => BASE_DIR . "/runtime/compiled/"
                    ));

                    return $volt;
                }
            ));
            return $view;
        });

        // 数据库
        $this->setShared('db', function () {
            
            // 数据库配置
            $db_config = self::getConfigInfo('database')->toArray();
            
            $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($db_config);

            $connection->setEventsManager(self::getEventsManager());

            return $connection;
        });

        // 元数据管理
        $this->setShared('modelsMetadata', function () {
            // Instantiate a metadata adapter
            $metadata = new \Phalcon\Mvc\Model\MetaData\Memory();
            
   
            // 用注解方式
            $metadata->setStrategy(new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations());

            return $metadata;
        });

        
        
        // 设置模型缓存服务
        $this->setShared('modelsCache', function () {
            // 缓存数据一天（默认设置）
            $frontCache = new \Phalcon\Cache\Frontend\Data([
                'lifetime' => 3600 * 24
            ]);

            // 用文件进行缓存 ,可自行改成其它
            $cache = new \Phalcon\Cache\Backend\File($frontCache, [
                "cacheDir" => BASE_DIR . "/runtime/cache/"
            ]);

            return $cache;
        });

        
        
        
        // 日志
        $this->setShared('log', function(){
                
            $save_dir = BASE_DIR .'/runtime/log/'.date('Ymd').'_new_mylog.txt';
            
            if (isset($_GET['_url']) )
                $url = $_GET['_url'];
            else
                $url = self::getRequest()->getServer('PATH_INFO');
            
            $log = new \Phalcon\Logger\Adapter\File($save_dir);
            $log->setFormatter(new \Phalcon\Logger\Formatter\Line('[%type%]%message%','Y-m-d H:i:s'));
            
            $log->begin();
            
            $log->log('------------------------['.date('Y-m-d H:i:s').']-------['.REQUEST_TIME_FLOAT.']-------------------------------',\Phalcon\Logger::DEBUG);
            
            $log->log('[url]'.$url.'   [ip]'.Util::getIp(),\Phalcon\Logger::INFO);

            $log->log('[post]'.json_encode($_POST),\Phalcon\Logger::INFO);
            
            
            return $log;    
        });
        
        
        
        //注入配置
        $this->setShared('config', function(){

            $config = new \Phalcon\Config\Adapter\Ini(BASE_DIR.'/config.ini');
            
            //我自己的本地配置
            $env_file = BASE_DIR.'/my.ini';
            
            if (is_file($env_file))
            {
                $env = new \Phalcon\Config\Adapter\Ini($env_file);
                
                //合并两份配置(优先用本地的配置)
                $config->merge($env);
            }
            
            return $config;
        });
        
        
    }

    /**
     * #获取的DI
     * @author WYY 2019-12-02 09:46
     * @return self
     */
    public static function getDi()
    {
        return self::getDefault();
    }

    
    /**
     * #db
     * @author WYY 2021-02-04 17:53
     * @return Mysql
     */
    public static function getConnection()
    {
        return self::getDi()->get('db');
    }
    
    
    /** 
     * #Request
     * @author  WYY 2021-02-05 10:25
     * @return Request
     */
    public static function getRequest() 
    {
        return self::getDi()->get('request');
    }
    
    
    /**
     * #Response
     * @author  WYY 2021-02-05 10:25
     * @return Response
     */
    public static function getResponse()
    {
        return self::getDi()->get('response');
    }
    
    
    /**
     * #Log
     * @author  WYY 2021-02-05 10:25
     * @return \Phalcon\Logger\Adapter\File
     */
    public static function getLog()
    {
        return self::getDi()->get('log');
    }
    
    
    /** 
     * #事件管理器
     * @author  WYY 2021-02-05 10:31
     * @return \Phalcon\Events\Manager
     */
    public static function getEventsManager() 
    {
        return self::getDi()->get('eventsManager');
    }
    
    /** 注解器
     * @author  WYY 2020年2月27日 下午5:16:44
     * @return \Phalcon\Annotations\Adapter\Memory
     */
    public static function getAnnotations()
    {
        return self::getDi()->get('annotations');
    }
    
    
    /**
     * #获取一个Transaction
     * @author  WYY 2020-05-14 11:54
     * @return \Phalcon\Mvc\Model\Transaction
     */
    public static function getTransaction()
    {
        
        return self::getDi()->get('transactionManager')->get();
    }
    
    /**
     * #获取一个TransactionManager
     * @author  WYY 2020-05-14 11:54
     * @return \Phalcon\Mvc\Model\Transaction\Manager
     */
    public static function getTransactionManager()
    {
        
        return self::getDi()->get('transactionManager');
    }
    
    
    /**
     * #获取配置信息（整体）
     * @author  WYY 2020-06-19 09:25
     * @param string $path 路径名(Exam:sys.appname)
     * @return \Phalcon\Config\Adapter\Ini
     */
    public static function getConfig()
    {
        return self::getDi()->get('config');
    }
    
    /**
     * #获取配置信息(部分)
     * @author  WYY 2020-06-19 09:25
     * @param string $path 路径名(Exam:sys.appname)
     * @return \Phalcon\Config\Adapter\Ini|string
     */
    public static function getConfigInfo($path)
    {
        return self::getConfig()->path($path);
    }
    
    
    /**
     * #获取缓存实例
     * @author WYY 2018年9月5日 上午10:27:18
     * @return \Phalcon\Cache\Backend\File
     */
    public static function getCache()
    {
        return self::getDi()->get('modelsCache');
    }
    
    /**
     * #获取模型缓存
     * @author WYY 2018年9月5日 上午10:27:18
     * @return \Phalcon\Cache\Backend\File
     */
    public static function getModelsCache()
    {
        return self::getDi()->get('modelsCache');
    }
    
    /**
     * #获取modelsManager
     * @author  WYY 2020-06-19 15:49
     * @return \Phalcon\Mvc\Model\Manager
     */
    public static function getModelsManager()
    {
        return self::getDi()->get('modelsManager');
    }
    
    
    
    
    
    
    
    
    
}

