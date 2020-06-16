<?php
namespace app\base;

use app\libary\Util;


/**
 *
 * @author Administrator
 *        
 */
abstract class BaseService
{
    
    abstract static function findOneByid($id);
    
    abstract static function saveOne($data, $id = 0, $white_list = null); 

    abstract static function delOne($id); 

    
    
    /** 
     * 查找一条已经存在的记录
     * @author  WYY 2020年3月12日 下午4:56:06
     * @param int $id
     * @return BaseModel
     * @throws \Exception
     */
    public static  function findTrueOne($id) 
    {
        $one = static::findOneByid($id);
        
        if (empty($one))
            Util::throwException(10, '无效的id');
       
        return $one;    
    }
    
    
    
}

