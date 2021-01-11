<?php
namespace app\plugin\Myplugin;

use app\base\BaseController;

/**
 * #尝试写一个插件
 * #插件控制器
 * @author WYY  2021-01-11 14:53
 */
class IndexController extends BaseController
{
    
    
    public function testAction() 
    {
        echo 'MypluginController::test';
    }
    
    
    
    public function test2Action()
    {
        echo 'MypluginController::test2';
    }
    
    
}

