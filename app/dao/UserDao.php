<?php
namespace app\dao;

use app\base\BaseDao;
use app\model\UserModel;
use Phalcon\Mvc\Model\Query\Builder;
use app\libary\Util;

/**
 * #user的数据库查询
 * <li>由于在父类已经封装的常用的操作，所以在这里不会有太多操作
 * <li>但复杂的数据库操作还是在这里手动写一写
 * @author WYY  2020-06-16 16:23
 */
class UserDao extends BaseDao
{
    
    /** 
     * #用phql查询一个用户
     * <li>phal使用的例子
     * @author  WYY 2020-06-19 10:51
     * @param int $id
     * @return \Phalcon\Mvc\Model\Resultset\Simple|UserModel[]
     */
    public static function findOndByPhql($id) 
    {
        
        $phql = 'select * from '.UserModel::class .' where id = :id:';
        
        $param['id'] = $id;
        
        return self::excutePhql($phql, $param);
    }
    
    
    /** 
     * #用builder查询一个例子
     * @author  WYY 2020-06-19 10:59
     * @param int $id
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function findOneByBuilder($id) 
    {
        $builder = self::getBuilder();
        
        $builder->where('id = :id:');    
        
        $param['id'] = $id;      
        
        return self::execute($builder,$param);
    }
    
    
    /** 
     * #用phql查询一个用户,使用缓存
     * @author  WYY 2020-06-19 15:47
     * @param int $id
     * @return \Phalcon\Mvc\Model\Resultset\Simple|UserModel[]
     */
    public static function findOndByPhqlCache($id)
    {
        
        $phql = 'select * from '.UserModel::class .' where id = :id:';
        
        $param['id'] = $id;
        
        
        $modelManage = Util::getModelsManager();
        
        $cacheOptions = [
            'key'      => 'usermodel_findOndByPhqlCache_'.$id,
            'lifetime' => 3600,
        ];
        
        return $modelManage->createQuery($phql)->cache($cacheOptions)->execute($param);
         
    }
    
    
    
    /**
     * #用builder查询一个例子 (使用缓存)
     * @author  WYY 2020-06-19 10:59
     * @param int $id
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function findOneByBuilderCache($id)
    {
        $builder = self::getBuilder();
        
        $builder->where('id = :id:');
        
        $param['id'] = $id;
        
        //这些参数可以统一处理的
        $cacheOptions = [
            'key'      => 'usermodel_findOneByBuilderCache_'.$id,
            'lifetime' => 3600,
        ];
        
        return $builder->getQuery()->cache($cacheOptions)->execute($param);
    }
    
    
    
}

