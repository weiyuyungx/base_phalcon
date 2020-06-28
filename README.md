[TOC]

# base_phalcon

基于phalcon3.4封装,帮助你快速开发项目.

# 环境要求
- php7.0及以上
- phalcon3.4


# 功能概览

1,根据数据表，一键快速生成对应的Model,Dao,Service

2,生成的Model已经二次封装, 自动验证字段类型大小长度

3,规范定义每一层的任务与权责

4,统一处理抛错。不用关心下层调用错误返回值

5,用户登录的例子来更好体现整体功能


## 分层结构

```
 权限控制器  (BaseXXController)  本控制器仅处理权限问题。本层控制器为树状继承
业务控制器(XXController) 继承一个权限控制器以获得某个权限（如登录权限)。 处理具体的业务
 
 数据业务层(Service)      数据业务逻辑层。本层可以被任务地方调用。
 数据操作层(Dao)       数据库查询层,仅做查询工作。不处理业务的事。本层只允许对应的Service调用
 数据表示层(Model)     数据表在PHP上的表示。把数据库里省略的内容表示完整，对上层屏蔽一些无感字段
 视图层(View)         简单使用(一般是前后端分离)
```

## 使用规范

1.禁止跨层调用

2.各层保持职责单一性 

3.权限与业务分离

4.已经有例子，具体请参考例子

## 配置文件  

1.公共配置在config.ini中

2.本地配置在my.ini中,本地配置会顶替公共配置的相同部份	


# 例子介绍

## BaseModel/BaseDao/BaseService的封装

1,baseModel保存时自动验证字段注解信息

2,baseModel预写了事件并处理事件中抛的错

3,baseDao封装了一些常用方法

4,baseService中实现了常用的方法


## 一键生成Model/Dao/Service

1,访问 {host}/Helper/test 会自动生成

2,可以微调字段注解内容，使得表示更精确

```
  自动生成注解示例:
  
  
    /**
     * 手机号
     * @Column(type='string', length=16, nullable=false)
     */
    public $mobile;
    
    
  微调注解示例:    
  
    /**
     * 性别
     * <li>0,未知，1男，2女。
     * <li>用注解限制其取值范围。好处是IDE可以弹出帮助查看
     * @Column(type='integer', nullable=false ,in='0,1,2')
     */
    public $sex;
 
```

3,继承父类BaseModel，获得自动验证注解信息

```
abstract class BaseModel extends \Phalcon\Mvc\Model
{
    //验证子类的注解信息 
    public function validation();
}
```



## 权限控制器

1,以用户登录权限为例，在BaseUserController里拦截未登录操作。

   意味着本控制器的操作都需要登录才能操作（登录权限）。

```

 BaseUserController
 
    /**
     * #前置方法
     * <li>拦截非登录用户
     */
    public function beforeExecuteRoute()
    {
        //父类优先拦截
        if ( parent::beforeExecuteRoute() === false)
            return false;

        $uid = $this->getUid();

        if ($uid <= 0)
        {
            //跳到Error处理
            $this->dispatcher->forward([
                'controller' => 'Error',
                'action' => 'unLogin',
                'namespace' => 'app\base'
            ]);
            
            return false; //表示拦截
        }
    }
```


2,业务控制器继承BaseUserController后，获得自动拦截。这样业务与权限分开。

  业务控制器可以专心业务，不用关心权限问题。

```

class FeeController extends BaseUserController
{
}

```


# 整体说明

1,本次封装是由作者平时项目中剥离出来

2,目的主要是快速开发。

3,若要增加功能模块，可以联系作者


# 作者
52741575@qq.com
weikkk 广西南宁

