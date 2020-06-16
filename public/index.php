<?php
use Phalcon\Mvc\View;
use Phalcon\mvc\Dispatcher;
use Phalcon\Http\Request;
use Phalcon\Events\Manager;
use Phalcon\Session\Adapter\Redis;
use app\libary\Util;
use app\libary\Mylog;
use app\libary\Myprofiler;


date_default_timezone_set('Asia/Shanghai');
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('BASE_DIR', str_replace('\\', '/', dirname(__DIR__)));
define('PUBLIC_DIR', str_replace('\\', '/', __DIR__));

set_error_handler('wei_error_handler',E_ALL);



/**
 * 入口文件
 * 如果是apache，请把入口放到public目录下(默认)
 * 如果是nginx,请把入口放到与public同级，并修改PUBLIC_DIR常量的值
 *
 */



// 注册命名空间
$Loader = new \Phalcon\Loader();

$Loader->registerNamespaces( [ 'app' => BASE_DIR . "/app/"])->register();

  
// 日志记录器
$mylog = new \app\libary\Mylog();


try
{

    $di = new Phalcon\DI\FactoryDefault();

    $di->set('mylog', $mylog);

    //设置路由
    $di->set('router',
        function ()
        {

            $router = new \Phalcon\Mvc\Router(false);

            $router->removeExtraSlashes(true);

//             //默认无参
            $router->add('/', array(
                'controller' => 'Index',
                'action' => 'index',
                'namespace' => 'app\controller'
            ));

            $router->add('/:controller/:action/:params', array(
                'controller' => 1,
                'action' => 2,
                'params' => 3
            ));
            
            //用户权限
            $router->add('/user.([a-zA-Z]+)/:action/:params', array(
                'controller' => 1,
                'action' => 2,
                'params' => 3,
                'namespace' => 'app\controller\user'
            ));
            
            // 不符合路由时
            $router->notFound([
                'controller' => 'Error',
                'action' => 'notFound',
                'namespace' => 'app\base'
            ]);

            return $router;
        },true);

    
    //注入session
    $di->set('session',
        function ()
        {
            $session = new \Phalcon\Session\Adapter\Files([
                'uniqueId' => 'base_phalcon'
            ]);
            
            $session->start();

            return $session;
            
        },true);

    
    //事件管理器
    $di->set('eventsManager', function()
    {
        $eventsManager = new Manager();
        $eventsManager->attach('dispatch',
            function ($event, $dispatcher, $exception)
            {
                //控制器执行抛错时
                if ($event->getType() == 'beforeException')
                {
                    $dispatcher->forward([
                        'controller' => 'Error',
                        'action' => 'exception',
                        'namespace' => 'app\base',
                        "params" => array(
                            'exception' => $exception
                        ),
                    ]);
                }
                
                //分发器找不到对应的controller/action
                if ($event->getType() == 'beforeNotFoundAction')
                {
                    $dispatcher->forward([
                        'controller' => 'Error',
                        'action' => 'notFound2',
                        'namespace' => 'app\base'
                    ]);
                }
                
                
                
        });
        
        return $eventsManager;
    },true);
    
   
    
    //分发器注入事件管理器
    $di->set("dispatcher",
        function () use ($di)
        {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace('app\controller');

            $dispatcher->setEventsManager($di->get('eventsManager'));

            return $dispatcher;
        },true);

    
    
    
    //视图
    $di->set("view",
        function ()
        {
            $view = new View();
            $view->setViewsDir(BASE_DIR . "/app/view/");
            
            $view->registerEngines(
                array(
                    // '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
                    '.html' => function ($view, $di)
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
    },true);
    

    // 数据库
    $di->set('db',
        function () use ($mylog, $di)
        {

            $db_config = [
                "host" => "192.168.1.21",
                "username" => "root",
                "password" => "root",
                "dbname" => "test",
                'charset' => 'utf8',
                'persistent' => true
            ];

            $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($db_config);

            $eventsManager = $di->get('eventsManager');

            $profiler = new Myprofiler();

            $profiler->mylog = $mylog;

            // 监听数据库的事件
            $eventsManager->attach('db',
                function ($event, $connection) use ($profiler)
                {
                    if ($event->getType() == 'beforeQuery')
                    {
                        // 操作前启动分析
                        $profiler->startProfile($connection->getSQLStatement());
                    }
                    if ($event->getType() == 'afterQuery')
                    {
                        // 操作后停止分析
                        $profiler->stopProfile();
                    }
                });

            $connection->setEventsManager($eventsManager);

            return $connection;
        },true);

    
    //元数据管理
    $di->set('modelsMetadata',function ()
    {
        // Instantiate a metadata adapter
        $metadata = new \Phalcon\Mvc\Model\MetaData\Memory();
        
        //用注解方式
        $metadata->setStrategy(
            new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations()
            );
        
        return $metadata;
    },true);

    
    /**
     *
     * @var Request $request
     */
    if (isset($_GET['_url']) )
        $uri = $_GET['_url'];
    else
        $uri = $di->get('request')->getServer('PATH_INFO');

    $application = new \Phalcon\Mvc\application($di);

    
    $response = $application->handle($uri);


    $content = $response->getContent();

    $response->send($content);

    $mylog->setLog('[content]' . PHP_EOL . $content, \Phalcon\Logger::INFO);
    $mylog->save();

    // echo $content;
}
catch (\Throwable $e) // 意外抛错(语法类型的错)
{

    $json['code'] = $e->getCode() + 9000000;
    $json['msg'] = '服务器异常';
    $json['debug']['time'] = date('Y-m-d H:i:s');
    $json['debug']['errmsg'] = $e->__toString();

    weiout($json);
}



//语法错误 未定义的变量，不存在key
function wei_error_handler($errno, $errstr, $errfile, $errline)
{

    $json['code'] = $errno + 8000000;
    $json['msg'] = '服务器语法错误';
    $json['debug']['time'] = date('Y-m-d H:i:s');
    $json['debug']['errmsg'] = $errstr . '####' . $errfile . '###' . $errline;


    weiout($json);

    exit(); // 只输出一次就中断
}



function weiout($json)
{
    $di = new Phalcon\DI\FactoryDefault();
    $origin = $di->get('request')->getServer('HTTP_ORIGIN');
    
    $response = $di->get('response');
    
    $response->setContentType('application/json', 'UTF-8');
    $response->setContent(json_encode($json,4095));
    $response->setHeader('Access-Control-Allow-Credentials', 'true');
    $response->setHeader('Access-Control-Allow-Origin', $origin);
    $response->setHeader('Access-Control-Allow-Methods', 'PUT,POST,GET,DELETE,OPTIONS');
    $response->setHeader('Access-Control-Allow-Headers', 'x-requested-with,content-type,x_Requested_With');
    $response->setHeader('appname', 'bzt3.0');
    
    
    
    $mylog = new Mylog();
    // 记录日志
    $mylog->setSave_dir(BASE_DIR . '/runtime/error/' . date('Ymd') . '_err.txt'); // 出错时的地址
    $mylog->setLog('[code]' . $json['code'], \Phalcon\Logger::ERROR);
    $mylog->setLog('[msg]' . $json['debug']['errmsg'], \Phalcon\Logger::ERROR);
    
    $mylog->save();
    
    
    
    
    $response->send();
}


