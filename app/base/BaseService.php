<?php
namespace app\base;

use app\libary\Util;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;


/**
 *
 * @author Administrator
 *
 */
abstract class BaseService
{
    
    /**
     * #保存一条
     * @author  WYY 2020-11-13 17:54
     * @param array $data
     * @param number $id
     * @param array $white_list
     * @throws \Exception
     * @return boolean
     */
    public static function saveOne($data, $id = 0, $white_list = null)
    {
        $model = self::getModel($id);
        
        return $model->save($data,$white_list);
    }
    
    
    
    /**
     * #删除一条
     * @author  WYY 2020-11-13 17:43
     * @param int $id
     * @return boolean
     * @exception \Exception
     */
    public static function delOne($id)
    {
        if ($id <= 0){
            Util::throwException(1004, '无效的ID');
        }
        
        return self::getModel($id)->delete();
    }
    
     
    
    /**
     * #生成一个新Model
     * @author  WYY 2020-11-16 09:53
     * @return Model
     */
    public static function getNewModel()
    {
        $class = Util::serviceNameToModelName(static::class);
        
        return new $class();
    }
    
    
    /** 
     * #获取一个model
     * @author  WYY 2021-01-13 11:03
     * @param number $id
     * @return \Phalcon\Mvc\Model
     * @exception \Exception
     */
    public static function getModel($id = 0) 
    {
        if ($id > 0){
            
            $builder = new Builder();
            $class = Util::serviceNameToModelName(static::class);
            $builder->from($class);
            
            $builder->andWhere('id = :id:',['id'=>$id]);
            $builder->limit(1);
            
            $model = $builder->getQuery()->execute();
         
         
            if ($model->count() == 0){
                Util::throwException(1003, '不存在的ID');
                
            }else {
                return $model->getFirst();
            }
                  
        }else{
            return self::getNewModel();
        }
    }
    
    
}

