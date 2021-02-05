<?php
namespace app\controller;

use app\base\BaseController;
use app\service\UserService;
use app\dao\UserDao;
use app\libary\Page;
use app\model\UserModel;
use app\libary\Util;
use Phalcon\Di;
use app\service\OrderService;
use app\dao\OrderFeeDao;
use app\service\OrderFeeService;


/**
 * 首页
 * <li>demo例子
 * @author WYY 2020-03-31 15:37
 */
class IndexController extends BaseController
{

    /**
     */
    public function indexAction()
    {
        echo 'base_phalcon';
    }

    /**
     *
     * @author WYY 2020-03-31 16:01
     */
    public function testAction()
    {
 
        
 
        
        
    }

    
    /** 
     * #保存一个用户
     * <li>demo
     * @author  WYY 2020-06-16 15:25
     * @return \Phalcon\Http\ResponseInterface
     */
    public function saveUserAction() 
    {
        $id = $this->_post('id');
        
        $data = $this->_post(null);
        
        unset($data['id']);
        
        /**
         * 注意：控制器的作用是接收/响应用户的请求。
         * 控制器无权对数据进行处理。
         * 控制器收集用户的请求数据，传递给service即可
         * 
         * 
         * 
         */
        

        $result = UserService::saveOne($data,$id);
        
        return $this->outResult($result);
    }
    
    
    
    /** 
     * #保存用户
     * <li>第二个例子
     * @author  WYY 2020-06-16 17:09
     */
    public function saveUser2Action() 
    {
       // $user = new UserModel();  //有些人喜欢这么用
       
        $user = UserService::findOneByid(4);
        
        $user->headimg = 'xx.jpg';
        $user->sex = 1;
        $user->passwd = '123456';
        $user->name = 'aaa15';
        $user->unionid = 'xcvvvdddeee';
        $user->mobile = '13800138105';
        
        
        $result = $user->save();
        
        //这么用的话，IDE可以自动提示字段的信息,文档，注解。不容易写错，对字段的取值也清楚
        //但是这么直接保存，跨过了Service。也就是可能会跨过一些逻辑处理
        //所以，这种写法有好有坏。
        //在业务逻辑不复杂的情况，可以这么用。如果比较复杂，最好是通过service处理
        
        
        
        
        return $this->outResult($result);
    }
    
    
    
    
    /** 
     * #获取用户资料
     * <li>demo
     * @author  WYY 2020-06-16 15:27
     * @return \Phalcon\Http\ResponseInterface
     */
    public function userInfoAction() 
    {
        $uid = $this->_post('uid');
        
        /*
         * 注意：在控制器只能调用service层处理(保存)数据
         * 不允许跨层。即不允许直接调用Dao层的方法
         * 若 $user = UserDao::findOne($id); 这是跨层调用。不允许，不允许。不允许
         * UserDao只允许UserService调用,其它XXserivce也不能调用
         * 任何类可以调用Service
         */
        
        
        $user = UserService::findOneByid(4);
        
        /*
         *在这里最终会输出JSON。所以会自动调用toArray()
         *也就是会自动加上常用数据，也屏蔽掉敏感数据
         *@see UserModel::toArray() 
         */
        
        
        return $this->ok($user);
    }
    
    
    
    /** 
     * #查找user列表
     * <demo>
     * @author  WYY 2020-06-16 17:04
     * @return \Phalcon\Http\ResponseInterface
     */
    public function findUserAction() 
    {
        $mobile = '13';  //模糊查找
        
        $page = new Page();
        $page->setCur_page(1);  //当前页
        $page->setPage_size(3); //每页数量
        
        //用户的列表
        $list = UserService::findListByMobile($mobile, $page);
        
        
        //这里最后也是输出JSON。所以也调用了UserModel::toArray()
        //也能自动地添加常用信息。也能自动屏蔽隐私字段
        
        return  $this->output(0, 'ok' ,$list ,$page);
       
    }
    
    
    
    
    
    
    
    /**
     * 视图例子
     * <li>demo
     */
    public function viewAction() 
    {
        
        
        $this->view->setVar('name', 'weikkk');
    }
    

    /** 
     * #phql/builder查询的例子
     * @author  WYY 2020-06-19 10:55
     */
    public function phqlTestAction() 
    {
        //这里查出来都是列表，取第一条
        $user_1 = UserService::phqltest(4);
        $user_2 = UserService::buildertest(4)->getFirst();
        
        
        $data['user_1'] = $user_1;
        $data['user_2'] = $user_2;
        
        //没被屏蔽的字段，可以直接取
        //IDE有字段属性提示，友好。被屏蔽的字段(private)不会提示出来
        //一般按这里的方法拿
        $data['user_1_mobile'] = $user_1->mobile;
        
       
        
        //被屏蔽的字段只能这么拿。
        $data['user_1_salt'] = $user_1->getAttr('salt');
        

        
        return $this->ok($data);
    }
    
 
    
    /** 
     * #修改密码的例子
     * @author  WYY 2020-06-19 14:49
     */
    public function editPasswdAction() 
    {
        $user = UserService::findOneByid(4);
        
        /*
         * #Model层对passwd屏蔽了加盐/哈希等过程，这里无感知
         * #这里修改passwd，就当成明文使用
         */
        $user->passwd = '123456789';
        
        $result = $user->save();
        
        return $this->outResult($result);
    }
    
    
    /** 
     * #使用缓存的例子
     * @author  WYY 2020-06-19 15:26
     */
    public function cacheTestAction() 
    {
        $cache = Util::getCache();
        
        $value = $cache->get('mytestcache'); //获取
        
        var_dump($value);
        
        //$cache->save('mytestcache','weikjkjkjkj'); //保存
        
    }
    
    
    /** 
     * #查询使用缓存的例子
     * @author  WYY 2020-06-19 16:13
     */
    public function queryCacheAction() 
    {
        
        //使用缓存
        $data['phql'] = UserService::findOndByPhqlCache(4);
        $data['builder'] = UserService::findOneByBuilderCache(5);
        
        
        
        return $this->ok($data);
    }
    
    
    
    public function infoAction() 
    {
        echo phpinfo();
    }

    
}

