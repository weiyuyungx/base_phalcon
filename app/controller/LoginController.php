<?php
namespace app\controller;

use app\base\BaseController;
use app\service\UserService;


/**
 * 登录相关
 *<li>例子
 * @author Administrator
 *        
 */
class LoginController extends BaseController
{

  
    
     /** 
      * #登录
      * @author  WYY 2020-06-16 11:46
      * @return \Phalcon\Http\ResponseInterface
      */
     public function loginAction() 
     {
         $name = $this->_post('name');
         $passwd = $this->_post('passwd');
         
         
         $user = UserService::findOneByName($name);
         
         if (empty($user))
             return $this->output(3, '不存在的用户名');
         
         //判定passwd是不是有效的
         //当明文使用。无感知哈希加盐等
         if ($user->isRealPasswd($passwd))
         {
             $this->session->set('uid', $user->id);
             return $this->ok();
         }
         else 
         {
             return $this->output(2, '帐号密码错误');
         }

     }



     /** 
      * #退出登录
      * @author  WYY 2020-06-16 11:49
      */
     public function logoutAction()
     {
         $this->session->remove('uid');
         
         return $this->ok();
     }


}

