<?php
use Phalcon\Mvc\View;
use Phalcon\mvc\Dispatcher;
use Phalcon\Http\Request;
use Phalcon\Events\Manager;
use Phalcon\Session\Adapter\Redis;
use app\libary\Util;
use app\libary\Mylog;
use app\libary\Myprofiler;
use app\libary\Mydi;


date_default_timezone_set('Asia/Shanghai');
define('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
define('BASE_DIR', str_replace('\\', '/', dirname(__DIR__)));
define('PUBLIC_DIR', str_replace('\\', '/', __DIR__));
define('NOW_TIME', time());
define('REQUEST_TIME_FLOAT', $_SERVER['REQUEST_TIME_FLOAT']);


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

try
{
    $di = new Mydi();

    //uri 任意取一个
    if (isset($_GET['_url']) )
        $uri = $_GET['_url'];
    else
        $uri = $di->get('request')->getServer('PATH_INFO');

    $application = new \Phalcon\Mvc\application($di);

    
    $response = $application->handle($uri);


    $content = $response->getContent();

    echo $content;
    
    
    //日志处理
    $log = Mydi::getLog();
    
    $log->log('[content]' . PHP_EOL . $content, \Phalcon\Logger::INFO);
    
    $log->commit();//提交

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
    
    
    $response->send();
    
    //错误日志
    $log = new \Phalcon\Logger\Adapter\File(BASE_DIR . '/runtime/error/' . date('Ymd') . '_err.txt');
    
    $log->begin();
    
    $log->log('[code]' . $json['code'], \Phalcon\Logger::ERROR);
    $log->log('[msg]' . $json['debug']['errmsg'], \Phalcon\Logger::ERROR);
    
    
}


