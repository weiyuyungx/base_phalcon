<?php
namespace app\base;

use app\base\BaseController;

/**
 * #错误处理的控制器
 *<li>不允许用户直接访问该控制器,只允许通过转发来该控制器
 *<li>所以放在这里.若不顺眼，也可能放到其它地方
 * @author Administrator
 * <li>扩展：也可以在controller下建一个目录，放置无路由的控制器。（即不可url访问的）
 * <li>有些业务需求是不允许直接访问，只能通过某个中间控制器转发。
 * <li>比如有个临时促销活动的控制器 。会有三个页面，活动前，活动中，活动后。这三个页面有独立的Action/view
 * <li>那么就把该控制器放到无路由区。由其它某个入口控制器判定时间后，转发到对应的活动(前中后).   
 * <li>也有人觉得麻烦。但这逻辑很清析
 */
class ErrorController extends BaseController
{

    /** 
     * 404不存在的页面
     * <li>路由不匹配引起
    * @author WYY  2020-01-06 16:41  
    * @return \Phalcon\Http\ResponseInterface
    */
    public function notFoundAction() 
    {
        return $this->output(404, 'not found 404 .....(router)');
    }
    
    
    /** 404不存在的页面
     * <li>不存在对应的controller/actoin引起
     * <li>为了区分这两种类型的404，而特意分开
     * @author WYY  2020-01-06 16:41
     * @return \Phalcon\Http\ResponseInterface
     */
    public function notFound2Action()
    {
        return $this->output(404, 'not found 404 .....(disp)');
    }
    
    
    /** 未登录
    * @author WYY  2020-01-07 09:47  
    */
    public function unLoginAction() 
    {
        return $this->output(99,'未登录');
    }
    


    /** 输出自定义的错误类型
     * @author  WYY 2020年2月25日 下午5:27:44
     * @return \Phalcon\Http\ResponseInterface
     */
    public function dispatcherAction() 
    {
        $code = $this->dispatcher->getParam('code') + 1000000;
        $msg = $this->dispatcher->getParam('msg');
        
        return $this->output($code,$msg);
    }
    
    
    
    /** 控制器抛错统一处理
     * <li>不包括throwable类型的异常</li>
     * <li>不包括语法类型的错</li>
     * @author  WYY 2020年3月17日 上午10:31:33
     * @return \Phalcon\Http\ResponseInterface
     */
    public function exceptionAction() 
    {

        /**
         * @var \Exception $exception
         */
        $exception = $this->dispatcher->getParam('exception');
        
        $data = [];
        if (is_numeric($exception->getCode()))
        {
            $data['code'] = $exception->getCode();
            $data['msg'] = $exception->getMessage();
        }
        else 
        {
            $data['code'] = 19;
            $data['msg'] = $exception->getCode().$exception->getMessage();
        }

        
        
        if ($exception instanceof \PDOException)  //数据库抛的错
        {
            $code = $exception->errorInfo[1];
            
            //数据库类型错
            if ($exception->getCode() == 23000)
            {
                if ($code == 1451)
                {
                    $data['msg'] = '外键限制，不能删除';
                }
                elseif ($code == 1452)
                {
                    $data['msg'] = '保存失败，不存在的外键值';
                }
                else if($code == 1062) 
                {
                    $data['msg'] = '重复记录冲突';
                }
                    

            }

            $data['code']  += 2000000;
   
        }
        else //其它类型的错(只到exception,不包括throwable)
        {
            $data['code'] += 3000000;             
        }
        
        
        $data['debug']['time'] = date('Y-m-d H:i:s');
        $data['debug']['errmsg'] = $exception->__toString();
        
        return $this->json_out($data);
    }
    
    
    
    
    
    
    
    
    
}

