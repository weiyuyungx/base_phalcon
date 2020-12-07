{{php_tag}}

namespace {{name_space}};

use {{self_dao}} as SelfDao;
use {{full_model_name}} as SelfModel;
use app\libary\Util;

/**
 * 
 *  @author {{author}}  
 */
class {{service_name}} extends {{parent_class}}

{

   
    /** 
     * #查找一条
     * @author  WYY 2020-11-16 15:53
     * @param int $id
     * @param boolean $throwable 为空时是否抛错
     * @return SelfModel
     */
    public static function findOneByid($id , $throwable = false) 
    {
        return parent::findOneByid($id ,$throwable);
    }
    
    
    
 
}