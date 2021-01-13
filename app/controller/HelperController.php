<?php
namespace app\controller;

use app\base\BaseController;
use app\service\UserService;
use app\libary\Util;

/**
 * 助手工具
 * <li>生成一些常用文件(model,dao,service)
 * <li>快速开发
 * <li>正式环境请删除
 * <li>二期工程：会自动根据数据表外键生成对应的模型关系
 * @author Administrator
 *        
 */
class HelperController extends BaseController
{
    //这些参数使用默认值即可
    
    //model的配置
    private $dir =  BASE_DIR. '/app/model/';  //生成的路径(确保存在路径)
    private $name_space = 'app\model';  //类的name_space。命名空间最好与路径一致。要不然就要在$Loader写加载路径
    private $parent_class = '\app\base\BaseModel';  //父类
    
    //dao
    private $dao_dir =  BASE_DIR. '/app/dao/';  //生成的路径(确保存在路径)
    private $dao_name_space = 'app\dao';  //类的name_space。命名空间最好与路径一致。要不然就要在$Loader写加载路径
    private $dao_parent_class = '\app\base\BaseDao';  //父类
    
    //srvice
    private $service_dir =  BASE_DIR. '/app/service/';  //生成的路径(确保存在路径)
    private $service_name_space = 'app\service';  //类的name_space。命名空间最好与路径一致。要不然就要在$Loader写加载路径
    private $service_parent_class = '\app\base\BaseService';  //父类
    
    
    
    private $force_cover = false;  //是否强制覆盖。 （如果文件已经存在，将会被覆盖）
    
    

    /** 
     * #首页
     * @author  WYY 2020-06-18 14:59
     * @return string
     */
    public function indexAction()
    {
        return 'helper--index';
    }
    
    
    
    
    
    /** 
     * #生成所有
     * #测试
     * @author  WYY 2020-06-17 11:58
     */
    public function testAction() 
    {

        $data =  $this->createAll();
          
        return $this->ok($data);
    }
    
    
    
    /** 
     * #自动生成所有的
     * <li>根据数据表来生成
     * <li>生成model,dao,service
     * @author  WYY 2020-06-17 17:13
     */
    private function createAll() 
    {
        $tables = $this->getAllTable();
        
        $total = count($tables);
        $model_succ = 0;  //成功数
        $model_fail = 0;  //失败数
 
        $dao_succ = 0;  //成功数
        $dao_fail = 0;  //失败数
        
        $service_succ = 0;  //成功数
        $service_fail = 0;  //失败数
        
        
        foreach ($tables as $tab)
        {
            
            $name_data = $this->tbName2ModelName($tab['TABLE_NAME']); 
            
            /*
                          * 生成model。自动把第一个下划线视为表前缀
             */
            $result = $this->renderModel($tab['TABLE_NAME']);
            
            if ($result)
            {
                $model_succ ++;
            }
            else
            {
                $model_fail ++;
            }
            
            //生成dao
            $result = $this->renderDao($name_data['model_name']);
            
            if ($result)
            {
                $dao_succ ++;
            }
            else
            {
                $dao_fail ++;
            }
            
            
            //生成service
            $result = $this->renderService($name_data['model_name']);
            
            if ($result)
            {
                $service_succ ++;
            }
            else
            {
                $service_fail ++;
            }
            
        }
        
        //简单统计
        $data['tables_total'] = $total;
        $data['model_succ'] = $model_succ;
        $data['model_fail'] = $model_fail;
        $data['dao_succ'] = $dao_succ;
        $data['dao_fail'] = $dao_fail;
        $data['service_succ'] = $service_succ;
        $data['service_fail'] = $service_fail;
        
        
        return  $data; 
    }
    

    
    
    /** 
     * #渲染生成一个model
     * @author  WYY 2020-06-18 11:37
     * @param string $table_name
     */
    private function renderModel($table_name)
    {
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

        $view = $this->view;

        $view->setVar('name_space', $this->name_space);   
        $view->setVar('model_name', $table_name_data['model_name']);  
        $view->setVar('parent_class', $this->parent_class);
        $view->setVar('author', 'HelperTool auto at '.date('Y-m-d H:i:s'));
        $view->setVar('php_tag', '<?php');
        $view->setVar('suff', $table_name_data['suff']);
        $view->setVar('fields', $this->getFieldarr($table_name));
        
        //model模板   /view/helper/model.templet
        $content =   $view->render('helper', 'model')->getContent();
        ob_clean();
        

        return file_put_contents($file_name, $content); 
    }
    
    
    
    /**
     * #渲染生成一个DAO
     * <li>根据model_name来生成
     * @author  WYY 2020-06-18 12:48
     * @param string $model_name
     */
    private function renderDao($model_name)
    {
        $dao_name = str_replace('Model','Dao',$model_name);
        
        $file_name = $this->dao_dir.$dao_name.'.php';
        
        if (!$this->force_cover)
        {
            //判断model是否存在，不做覆盖处理
            if (is_file($file_name))
            {
                return false;
            }
        }
        
        $view = $this->view;
        
        $view->setVar('name_space', $this->dao_name_space);
        $view->setVar('full_model_name', $this->name_space.'\\'.$model_name);
        $view->setVar('model_name', $model_name);
        $view->setVar('dao_name', $dao_name);
        $view->setVar('parent_class', $this->dao_parent_class);
        $view->setVar('author', 'HelperTool auto at '.date('Y-m-d H:i:s'));
        $view->setVar('php_tag', '<?php');
        
        
        //dao模板   /view/helper/dao.templet
        $content =   $view->render('helper', 'dao')->getContent();
        ob_clean();
        
        
        return file_put_contents($file_name, $content);
    }
    
    
    /**
     * #渲染生成一个Service
     * <li>根据model_name来生成
     * @author  WYY 2020-06-18 12:48
     * @param string $model_name
     */
    private function renderService($model_name)
    {
        
        $service_name = str_replace('Model','Service',$model_name);
        $dao_name = str_replace('Model','Dao',$model_name);
        
        $file_name = $this->service_dir.$service_name.'.php';
        
        if (!$this->force_cover)
        {
            //判断model是否存在，不做覆盖处理
            if (is_file($file_name))
            {
                return false;
            }
        }
        
        $view = $this->view;
        
        $view->setVar('name_space', $this->service_name_space);
        $view->setVar('parent_class', $this->service_parent_class);
        $view->setVar('author', 'HelperTool auto at '.date('Y-m-d H:i:s'));
        $view->setVar('php_tag', '<?php');
        
        
        $view->setVar('self_dao', $this->dao_name_space.'\\'.$dao_name);
        $view->setVar('service_name', $service_name);
        $view->setVar('full_model_name', $this->name_space.'\\'.$model_name);
        $view->setVar('model_name', $model_name);
        
        
        
        
        //service模板   /view/helper/service.templet
        $content =   $view->render('helper', 'service')->getContent();
        ob_clean();
        
        
        return file_put_contents($file_name, $content);
    }
    
    
    /** 
     * #生成field的列表
     * @author  WYY 2020-06-18 11:57
     * @param string $table_name
     */
    private function getFieldarr($table_name) 
    {
        $list = [];
        
        $conn = Util::getConnect();
        
        $sql = 'DESC '.$table_name;
        
        $data = $conn->fetchAll($sql);
        
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
            
            //去掉tab符
            $list[] =  str_replace("    "," ",$temp);
        }
        
        
        return $list;
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

