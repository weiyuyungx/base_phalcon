<?php
namespace app\controller;

use app\base\BaseController;
use app\service\UserService;
use app\libary\Util;

/**
 * 助手工具
 * <li>生成一些常用文件(model)
 * <li>快速开发
 * <li>正式环境请删除
 * <li>二期工程：会自动根据数据表外键生成对应的模型关系
 * @author Administrator
 *        
 */
class HelperController extends BaseController
{
    //这些参数使用默认值即可
    
    private $dir =  BASE_DIR. '/app/model/';  //生成的路径(确保存在路径)
    private $name_space = 'app\model';  //类的name_space。命名空间最好与路径一致。要不然就要在$Loader写加载路径
    private $parent_class = '\app\base\BaseModel';  //父类
    private $force_cover = false;  //是否强制覆盖。 （如果文件已经存在，将会被覆盖）
    
    

    public function indexAction()
    {
        return 'helper--index';
    }
    
    
    
    /** 
     * #生成所有model
     * #测试
     * @author  WYY 2020-06-17 11:58
     */
    public function testAction() 
    {

        //将所有的数据表都生成对应的Model
        $this->createAllModel();
    }
    
    
    
    /** 
     * #生成所有的Model
     * @author  WYY 2020-06-17 17:13
     */
    private function createAllModel() 
    {
        $tables = $this->getAllTable();
        
        $total = count($tables);
        $succ = 0;  //成功数
        $fail = 0;  //失败数
        foreach ($tables as $tab)
        {
            //table_name处理
            $result = $this->createModel($tab['TABLE_NAME']);
            
            if ($result)
            {
                $succ ++;
                echo $tab['TABLE_NAME'].'--------create Model succ<br/>';
            }
            else
            {
                $fail ++;
                echo $tab['TABLE_NAME'].'###Fail<br/>';
            }
        }
        
        //简单统计
        echo '<hr/>';
        echo  'total: '.$total.'<br/>';
        echo  'succ: '.$succ.'<br/>';
        echo  'fail: '.$fail.'<br/>';
    }
    
    
    
    /** 
     * #生成一个Model
     * @author  WYY 2020-06-17 12:01
     * @param string $table_name 表名
     */
    private function createModel($table_name) 
    {
        //table_name处理
        $table_name_data = $this->tbName2ModelName($table_name);
        $file_name = $this->dir.$table_name_data['model_name'].'.php';
        
        
        if (!$this->force_cover)
        {
            //判断model是否存在，不做覆盖处理
            if (is_file($file_name))
            {
                return false;
            }   
        }
        
         //字段信息
         $field = $this->getFieldString($table_name);


         //类信息
         $str = $this->createModelClass($table_name_data['model_name'], $field);
         
         //去掉tab符    
         $str = str_replace("    ","",$str);

         //保存到文件
         return file_put_contents($file_name, $str);
    }
    
    
    /** 
     * #生成字段的信息
     * @author  WYY 2020-06-17 15:19
     * @param string $table_name
     */
    private function getFieldString($table_name) 
    {
        $conn = Util::getConnect();
        
        $sql = 'DESC '.$table_name;
        
        $data = $conn->fetchAll($sql);
        
        $table_name_data = $this->tbName2ModelName($table_name);
        
        
        /*
         *如果前缀都相同，可以统一抽取到父类
         *如果一个库里有多种前缀.请在Model目录里建以前缀为名的目录。(这里先不搞)
         */
        $str = "/**
                 * 数据表前缀
                 * @var string
                 */
                protected static \$db_pre = '{$table_name_data['suff']}';
                ";
        
        foreach ($data as $one)
        {
            $column = ''; //type='integer', nullable=false type='string', length=16,
            $Primary = '';
            $Identity = '';
            
            
            //类型判断
            if (strpos($one['Type'],'tinyint') !== false)
            {
                $column .= "type='integer' , max='127' ,min='-128'";
                
            }
            elseif (strpos($one['Type'],'int') !== false)
            {
                $column .= "type='integer'";
            }
            elseif (strpos($one['Type'],'varchar') !== false)
            {
                $mat = null;
                preg_match('/(\d{1,})/',$one['Type'],$mat);
                
                $column .= "type='varchar' , length='{$mat[0]}'";
                
            }
            elseif (strpos($one['Type'],'date') !== false)
            {
                $column .= "type='date'";
            }
            elseif (strpos($one['Type'],'text') !== false)
            {
                
                $column .= "type='text'";
            }
            elseif (strpos($one['Type'],'decimal') !== false)
            {
                //decimal(7,2)
                $mat = null;
                
                preg_match_all('/\d/',$one['Type'],$mat);
                
                $length = $mat[0][0];  //总长度
                $flo = $mat[0][1];  //小数点位
                
                $num = null;
                
                for ($i = 0; $i < $length; $i++) {
                    
                    if ($length - $i == $flo)
                    {
                        $num .= '.';
                    }
                    $num .= '9';
                }
                
                $max = '';
                $min = '';
                $column .= "type='decimal' ,max='{$num}' ,min='-{$num}'";
            }
            
            
            //判断可为空
            if ($one['Null'] == 'NO')
            {
                $column .= " ,nullable=false";
            }
            elseif ($one['Null'] == 'YES')
            {
                $column .= " ,nullable=true";
            }
            
            //默认值
            if (!is_null($one['Default']))
            {
                $column .= " ,default='{$one['Default']}'";
            }
            
            
            //判断主键
            if ($one['Key'] =='PRI')
            {
                $Primary = '@Primary';
            }
            
            //判断自增
            if ($one['Extra'] == 'auto_increment')
            {
                $Identity = '@Identity';
            }
            
            
            
            $temp = "
                /**
                 * # ";
            if ($Primary)
            {
                $temp .= "
                 * {$Primary}";
            }
            
            if ($Identity)
            {
                $temp .= "
                 * {$Identity}";
            }
            
            
            
            $temp .=   "
                 * @Column({$column})
                 */
                public \${$one['Field']};
                
                ";
            
            
            $str .= $temp;
        }
        
        
        return $str;
    }
    
    
    /** 
     * #生成class
     * @author  WYY 2020-06-17 16:02
     * @param string $model_name
     * @param string $field
     */
    private function createModelClass($model_name ,$field)
    {
        $now = date('Y-m-d H:i:s');
        
        $str = "<?php
            namespace {$this->name_space};
            
            
            /**
             *
             * @author(HelperTool auto at {$now})
             */
            class {$model_name} extends {$this->parent_class}
            {

                {$field}
            
            }
            
            ";
                      
        return $str;
    }
    
    /** 
     * #将table_name切分割
     * @author  WYY 2020-06-17 16:04
     * @param string $table_name
     * @example tb_user_order => bb_;user_order;UserOrderModel
     * @return string
     */
    private function tbName2ModelName($table_name) 
    {
        //第一个下划线的位置。用来分割前缀与表名
        $pos = strpos($table_name , '_');
        
        //前缀
        $data['suff'] = substr($table_name, 0 ,$pos+1);
        
        //短表名
        $data['short_table_name'] = substr($table_name, $pos+1);
        
        //Model名
        $data['model_name']   = Util::convertUnderline($data['short_table_name']).'Model';
        
        
        return $data;
    }
    
    
    /** 
     * #获取库里所有的表的信息
     * @author  WYY 2020-06-17 16:38
     * @return array
     */
    private function getAllTable() 
    {
        $conn = Util::getConnect();
        
        $config = $conn->getDescriptor();
        
        $dbname = $config['dbname'];
         
        
        $sql = 'select * from INFORMATION_SCHEMA.TABLES where table_schema="'.$dbname.'"';
        
        return $conn->fetchAll($sql);
    }
    
    
    
}

