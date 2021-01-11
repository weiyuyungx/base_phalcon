<?php
namespace app\base;

use app\libary\Util;
use Phalcon\Mvc\Model;


/**
 *
 * @author Administrator
 *
 */
abstract class BaseService
{
    
        /**
     * #查找一条
     * @author  WYY 2020-11-16 09:44
     * @param int $id
     * @param bool $throwable 为空时处理
     * @throws \Exception
     * @return Model
     */
    public static function findOneByid($id , $throwable = false)
    {
        $class = Util::serviceNameToModelName(static::class);
        
        
        $builder = new \Phalcon\Mvc\Model\Query\Builder();
        
        $builder->from($class);
        $builder->where('id = :id:',['id'=>$id]);
        
        $model =  $builder->getQuery()->setUniqueRow(true)->execute();
        
        if ($model)
            return $model;
        else {
            if ($throwable == true) {
                Util::throwException(11, '不存在的ID');
            }
            else {
                return null;
            }
        }
            
    }
    
    
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
        if ($id > 0)
        {
            $model = self::findOneByid($id,true);
        }
        else
        {
            //生成一个新对象
            $model = static::getNewModel();
        }
        
        return $model->save($data,$white_list);
    }
    
    
    
    /**
     * #删除一条
     * @author  WYY 2020-11-13 17:43
     * @param int $id
     * @return boolean
     */
    public static function delOne($id)
    {
        return self::findOneByid($id,true)->delete();
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
    
    
    
}

