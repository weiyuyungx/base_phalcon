{{php_tag}}

namespace {{name_space}};

use {{self_dao}} as SelfDao;
use {{full_model_name}};
use app\libary\Util;

/**
 * 
 *  @author {{author}}    
 */
class {{service_name}} extends {{parent_class}}

{

    /** 
          *  查找一条
     * @param  $id
     * @return {{model_name}}
     
     */
    public static function findOneByid($id)
    {
        return SelfDao::findOne($id);
    }
    
    /** 
          *  保存一条
     * @param array $data 要保存的数据
     * @param number $id  大于0表示修改
     * @param array $white_list  白名单
     * @throws \Exception
     * @return number|boolean
     */
    public static function saveOne($data, $id = 0 ,$white_list = null)
    {
        return SelfDao::saveOne($data, $id , $white_list);
    }
    
    
    /** 
          *   删除一条
     * @param  $id
     * @return boolean
     */
    public static function delOne($id)
    {
        return SelfDao::delOne($id);
    }
    

    /** 
          * 查找一条已经存在的记录
     * @param int $id
     * @return {{model_name}}   
     * @throws \Exception
     */
    public static  function findTrueOne($id) 
    {
        $one = static::findOneByid($id);
        
        if (empty($one))
            Util::throwException(11, '无效的id');
       
        return $one;    
    }
    
    
    
    
    
    
    
    
    
 
}