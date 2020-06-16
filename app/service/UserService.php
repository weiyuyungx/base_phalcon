<?php
namespace app\service;

use app\base\BaseService;
use app\dao\UserDao as SelfDao;
use Throwable;
use app\model\UserModel;
use app\libary\Util;
use app\libary\Page;

/**
 * user的业务层
 * <li>做业务相关的事
 * <li>不允许在这里读写数据库。这里参允许传参从DAO获取
 * @author WYY  2020-06-16 10:24
 */
class UserService extends BaseService
{

    /**
     * #查一条
     * <li>把return值写好，因为常用
     * {@inheritDoc}
     * @see \app\base\BaseService::findOneByid()
     * @author WYY 2020-06-16 10:23
     * @return UserModel
     */
    public static function findOneByid($id)
    {
        if ($id <= 0)
        {
            //在业务层里，明显知道ID必须是大于0的。所以直接返回NULL。也可以抛错。看作者设计
            return null;
        }
        else 
        {
            //要不要从缓存中拿啊？
            
            //如果业务不需要从缓存中拿。就直接去数据库拿
            
            return SelfDao::findOne($id);
        }
 
    }

    /** 
     * #保存一条
     * <li>简单地保存一条记录
     * @author  WYY 2020-06-16 10:40
     * @param array $data
     * @param number $id
     * @param array $white_list
     * @return number|boolean
     */
    public static function saveOne($data, $id = 0, $white_list = null)
    {
        return SelfDao::saveOne($data, $id, $white_list);
    }
    
    
    
    /** 
     * #保存一条
     * <li>模拟其它业务
     * <li>有可能从其它业务过来的保存逻辑处理
     * @author  WYY 2020-06-16 16:46
     * @param array $data
     * @param number $id
     * @param array $white_list
     * @return number|boolean
     */
    public static function saveOne2($data, $id = 0, $white_list = null)
    {
        /*
         * 模拟某个业务场景下的保存用户
         * 有可能自动生成一些数据
         * 有可能操作前后都需要做其它，合起来成一个事务
         */
        
        $conn = Util::getConnect();
        
        $conn->begin();
        
        
        try {
            
            //做其它业务.....
            //自动生成一些数据.....
            $data = $data;
            
            
            $result = SelfDao::saveOne($data, $id, $white_list);
            
            //又做一些业务
            
            $conn->commit();

            return $result;
            
        } catch (\Throwable $e) {
            
            $conn->rollback();
            
            throw $e; //继续往前抛。不在这里处理
        }
        
       //让你知道save有多种情况
    }
    

    /** 
     * #删除一条
     * @author  WYY 2020-06-16 10:42
     * @param int $id
     * @return boolean
     */
    public static function delOne($id)
    {
        if ($id <= 0)
        {
            Util::throwException(85, 'id非法');
        }
        else 
        {
            return SelfDao::delOne($id);
        }

    }
    
    
    /** 
     * #查找一条
     * @author  WYY 2020-06-16 11:44
     * @param string $name
     * @return UserModel
     */
    public static function findOneByName($name) 
    {
        $where['name'] = ['eq',$name];
        
        return SelfDao::findOneByWhere($where);
    }
    
    
    
    
    /** 
     * #根据手机号(模糊)分页查找
     * @author  WYY 2020-06-16 17:02
     * @param String $mobile
     * @param Page $page
     * @return \Phalcon\Mvc\Model\Resultset\Simple|UserModel[]
     */
    public static function findListByMobile(String $mobile,Page $page) 
    {
        $where['mobile'] = ['like','%'.$mobile.'%'];
        
        return SelfDao::findListByWherePage($where,$page);
    }
    
    
    
    
    
}

