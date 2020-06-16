<?php
namespace app\controller\user;

use app\base\BaseController;
use app\base\BaseUserController;

/**
 * #用户的费用相关
 * <li>业务控制器，需要user登录权限
 * <li>继承BaseUserController以获得user登录权限
 * <li>访问uri四合一      /user.Fee/XX(action)
 * <li>前缀user. 有三重意思。1，文件夹目录结构。2，权限。3继承的路线
 * @author weikkk
 *        
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
        $data['uid'] = $this->getUid(); //用户的信息
        
        $data['list'] = [1,2,3,4,5,6];  //消费的列表
        
        
        return $this->ok($data);
    }
    

}

