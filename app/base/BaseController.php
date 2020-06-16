<?php
namespace app\base;

use Phalcon\Http\Request;
use app\libary\Page;

/**
 *#控制器基类
 * 
 *@author Administrator
 *@property \Phalcon\Http\Request $request
 */
class BaseController extends \Phalcon\Mvc\Controller
{

    /** 
     * #伪构造
     * @author  WYY 2020-06-09 16:34
     */
    protected function onConstruct()
    {
       
    }

    
    /** 
     * #路由前置方法
     * <li>return false时拦截该请求
     * @author WYY  2020-01-07 10:19  
     */
    public function beforeExecuteRoute() 
    {
        
    }
    
    
    /**
     * #初始化 一个请求仅一次
     * <li>如处理跨域等
     * @author WYY 2018年11月20日 上午10:54:59
     */
    public function initialize()
    {
        $origin = $this->di->get('request')->getServer('HTTP_ORIGIN');

        $this->response->setHeader('Access-Control-Allow-Credentials', 'true');
        $this->response->setHeader('Access-Control-Allow-Origin', $origin);
        $this->response->setHeader('Access-Control-Allow-Methods', 'PUT,POST,GET,DELETE,OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'x-requested-with,content-type,x_Requested_With');
        $this->response->setHeader('appname', 'weikkk');
    }

    /**
     * #输出json
     *
     * @author WYY 2019-11-25 10:43
     * @param array $data
     * @return \Phalcon\Http\ResponseInterface
     */
    protected function json_out($data)
    {
        $data['debug']['time'] = date('Y-m-d H:i:s');


        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setJsonContent($data);

        return $this->response;
    }

    /** 
     * #输出错误类型代码
     * @author  WYY 2020年2月25日 下午5:22:08
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return \Phalcon\Http\ResponseInterface
     */
    protected function output($code, $msg, $data = [],Page $page = null)
    {
        $json['code'] = $code;
        $json['msg'] = $msg;

        if ($data)
            $json['data'] = $data;
        
        if ($page)
            $json['page'] = $page->toArray();

        return $this->json_out($json);
    }

    /** 
     * #输出操作结果
     * @author  WYY 2020年2月25日 下午5:23:52
     * @param  $result
     * @return \Phalcon\Http\ResponseInterface
     */
    protected function outResult($result)
    {
        if ($result)
            return $this->ok();
        else
            return $this->output(2, '操作失败');
    }

    /** 
     * #操作成功时输出
     * @author  WYY 2020年2月25日 下午5:23:04
     * @param array $data
     * @return \Phalcon\Http\ResponseInterface
     */
    protected function ok($data = null)
    {
        return $this->output(0, 'ok', $data);
    }

    /** 
     * #操作失败时输出
     * @author  WYY 2020年2月25日 下午5:23:26
     * @return \Phalcon\Http\ResponseInterface
     */
    protected function notok()
    {
        return $this->output(2, '操作失败');
    }
    
    
    
    
    /**
     * #转发到ErrorController
     * <li>只转一次
     * @author WYY 2020-01-06 16:40
     * @param string $msg
     * @param int $code
     */
    protected function toDispatcher($msg, $code)
    {
        if ($this->dispatcher->wasForwarded())
            return;

            
        $this->dispatcher->forward(
            [
                'controller' => 'Error',
                'action' => 'dispatcher',
                "params" => array(
                    'code' => $code,
                    'msg' => $msg
                ),
                'namespace' => '\app\controller',
            ]);
    }

    /**
     * POST简写
     * @author WYY 2020-01-06 16:40
     * @param $name
     * @return mixed
     */
    protected function _post($name)
    {
        return $this->request->getPost($name);
    }

    /**
     * GET简写 
     * @author WYY 2020-01-06 16:40
     * @param $name
     * @return mixed
     */
    protected function _get($name)
    {
        return $this->request->get($name);
    }
    

}

