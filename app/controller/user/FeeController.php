<?php
namespace app\controller\user;

use app\base\BaseController;
use app\base\BaseUserController;

/**
 * #用户的费用相关,用户的交费情况
 * <li>例子
 * <li>业务控制器，需要user登录权限
 * <li>继承BaseUserController以获得user登录权限
 * <li>访问uri四合一      /user.Fee/XX(action) 意义: 1，在user目录下。2，user权限。3继承BaseUserController
 * @author weikkk
 *        
 * <li>说明。这个用户的某个业务控制器。写这个业务的人，不需要关心登录逻辑，不担心登录逻辑的修改
 * <li>直接继承 BaseUserController来获取用户登录后的权限
 */
class FeeController extends BaseUserController
{
    
    
    /** 
     * #用户的费用列表
     * <li>访问uri /user.Fee/myFee
     * @author  WYY 2020-06-15 17:54
     * @return \Phalcon\Http\ResponseInterface
     */
    public function myFeeAction() 
    {
        //用户的uid
        //要从父类拿。不要私下直接session/jwt/redis拿
        $data['uid'] = $this->getUid(); 
        
        $data['list'] = [1,2,3,4,5,6];  //消费的列表
        
        
        return $this->ok($data);
    }
    

}

