<?php
namespace app\base;

use Phalcon\Mvc\Controller;
use app\service\UserService;


/**
 * 用户（登录权限）
 * <li>控制器的权限与业务分开
 * <li>权限控制器只做权限的判断。一般情况下不做业务。权限控制器不允许实例化,只能被子类(业务类)继承
 * <li>业务子类通过继承权限父母类而获得相应的权限。
 * <li>业务子类可以全身心做业务相关的事，不用关心权限。若有权限变动，换个父类继承即可
 * @author Administrator
 *        
 */
abstract class BaseUserController extends BaseController
{

    /**
     * #前置方法
     * <li>拦截非登录用户
     * @author WYY 2020-01-07 10:17
     */
    public function beforeExecuteRoute()
    {
        //父类优先拦截
        if ( parent::beforeExecuteRoute() === false)
            return false;

        $uid = $this->getUid();

        if ($uid <= 0)
        {
            $this->dispatcher->forward([
                'controller' => 'Error',
                'action' => 'unLogin',
                'namespace' => 'app\base'
            ]);
            
            return false; //表示拦截
        }
    }

    /**
     * #获取用户的UID
     * <li>子类继承后用该方法获取用户ID，不允许直接从SESSION，JWT，redis拿
     * <li>不允许在其它地方(如service,model)里直接拿登录相关数据。只能从这里拿,再传参
     * <li>子类继承拥有该方法，表明是已经登录的用户
     * @author WYY 2020-01-06 16:36
     * @return mixed
     */
    protected function getUid()
    {
        return $this->session->get('uid');
    }


    /** 
     * #用户的信息
     * <li>子类从这里拿。不要直接查数据库
     * @author  WYY 2020-06-16 11:48
     * @return \app\model\UserModel
     */
    protected function getUserInfo() 
    {
        return UserService::findOneByid($this->getUid());
    }
    
    

}

