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
    
}

