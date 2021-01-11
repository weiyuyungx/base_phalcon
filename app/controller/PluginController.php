<?php
namespace app\controller;

use app\base\BaseController;

/**
 * #插件控制器
 * #统一入口
 * @author WYY 2020-03-31 15:37
 */
class PluginController extends BaseController
{


    /** 
     * #所有插件的入口
     * #统一拦截转发
     * @author  WYY 2021-01-11 15:12
     */
    public function indexAction()
    {
        // 访问url例子 {{host}}/Plugin.Myplugin/Index/test2
        // 最后转发到 plugin/Myplugin/IndexController::test
        
        
        $PluginName = $this->dispatcher->getParam('PluginName');
        $PluginController = $this->dispatcher->getParam('PluginController');
        $PluginAction = $this->dispatcher->getParam('PluginAction');
        
        // 这里应该有插件管理器来管理是否允许转发
        // 判断有没有转发的权限
        // 如果未安装插件则不转发
        
        // 转发操作
        $this->dispatcher->forward([
            
            'controller' => $PluginController,
            'action' => $PluginAction,
            'namespace' => 'app\plugin\\'.$PluginName
        ]);
        
        
    }
    
    
    
}

