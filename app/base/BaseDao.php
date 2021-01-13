<?php
namespace app\base;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query\Builder;
use app\libary\Page;
use app\libary\Util;

/**
 * #对一些常用查询操作的封装
 * <li>只查询，不涉及update/create/delete
 * <li>不涉及业务逻辑
 * @author Administrator
 *
 */
abstract class BaseDao
{
    
    /**
     * 查找一条
     *
     * @author WYY 2018年11月20日 下午12:03:06
     * @param int $id
     * @return Model
     */
    public static function findOneByid($id,$throwable = false)
    {
        $builder = self::getBuilder();
        
        $builder->where('id = :id:', [
            'id' =>  $id
        ]);
        
        $model = self::executeOne($builder);
        
        if ($throwable && !$model){
            
            Util::throwException(1001, '不存在的ID');
            
        }else{
            return $model;
        }
        
    }
    
    
    
    /**
     * #查找全部
     *
     * @author WYY 2018年9月10日 上午10:43:14
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function getAll()
    {
        $builder = self::getBuilder();
        
        $builder->orderBy('id DESC');
        
        return self::execute($builder);
    }
    
    
    // end
    
    /**
     * 一组ID查找
     *
     * @author WYY 2018年9月20日 下午3:36:13
     * @param array $arr
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function getByIdIn($id_arr)
    {
        $builder = self::getBuilder();
        
        $builder->inWhere('id', $id_arr);
        
        return self::execute($builder);
    }
    
    /**
     * 获得默认的builder
     *
     * @author WYY 2018年12月29日 上午9:36:03
     * @return \Phalcon\Mvc\Model\Query\Builder
     */
    protected static function getBuilder($default = true)
    {
        $builder = new \Phalcon\Mvc\Model\Query\Builder();
        
        if ($default)
        {
            $builder->from(self::getModelClassName());
            $builder->orderBy('id DESC');
        }
        
        return $builder;
    }
    
    
    /**
     * 执行一条phql语句
     *
     * @author WYY 2018年9月10日 上午10:59:38
     * @param string $phql
     * @param array $param
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    protected static function excutePhql($phql, $param)
    {
        $modelsManager = Util::getModelsManager();
        
        return $modelsManager->createQuery($phql)->execute($param);
    }
    
    
    
    /**
     * 执行一条 builder语句
     *
     * @author WYY 2018年12月29日 上午9:42:40
     * @param \Phalcon\Mvc\Model\Query\Builder $builder
     * @param array $param
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    protected static function execute(\Phalcon\Mvc\Model\Query\Builder $builder, $param = null)
    {
        return $builder->getQuery()->execute($param);
    }
    
    /**
     * 执行一条 builder语句 返回一条查询
     *
     * @author WYY 2018年12月29日 上午9:42:40
     * @param \Phalcon\Mvc\Model\Query\Builder $builder
     * @param array $param
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    protected static function executeOne(\Phalcon\Mvc\Model\Query\Builder $builder, $param = null)
    {
        $builder->limit(1, 0);
        return self::execute($builder, $param)->getFirst();
    }
    
    /**
     * 计算总条数
     *
     * @author WYY 2019年3月5日 上午10:19:36
     * @param \Phalcon\Mvc\Model\Query\Builder $builder
     */
    protected static function getSumExecute($builder, $param = null)
    {
        $columns = $builder->getColumns();
        
        $builder->columns('count(*) as total');
        $row = self::executeOne($builder, $param);
        $total = $row['total'];
        
        $builder->columns($columns);
        
        return $total;
    }
    
    /**
     * 查找列表 （简单封装,分页）
     * @author WYY 2019年3月25日 上午10:47:01
     * @param array $where
     * @param Page $page
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function findListByWherePage($where, Page $page = null)
    {
        
        $builder = self::doWhere($where, self::getBuilder());
        
        return self::findListByBuilderPage($builder,$page);
    }
    
    
    /**
     * #查找列表 （简单封装,分页）
     * @author WYY 2019年3月25日 上午10:47:01
     * @param Builder $builder
     * @param Page $page
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public static function findListByBuilderPage(Builder $builder, Page $page)
    {
        if ($page){
            
            $page->setTotal(self::getSumExecute($builder));
            
            $builder->limit($page->getPage_size());
            $builder->offset($page->offset());
        }
        
        return self::execute($builder);
    }
    
    
    
    // end function
    
    /**
     * 查找一条 （简单封装）
     *
     * @author WYY 2019年3月25日 上午10:47:01
     * @param array $where
     * @param  $page
     * @return \Phalcon\Mvc\Model\Resultset\Simple
     */
    public  static function findOneByWhere($where)
    {
        
        $builder = self::doWhere($where, self::getBuilder());
        
        return self::executeOne($builder);
    }
    
    
    
    /** 统一处理builder
     * @author  WYY 2020年2月25日 上午11:54:11
     * @param array $where
     * @param Builder $builder
     * @return Builder $builder
     */
    private static function doWhere($where , $builder)
    {
        
        foreach ($where as $k => $v)
        {
            switch ($v[0])
            {
                case 'eq':
                    $builder->andWhere("{$k} = :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'neq':
                    $builder->andWhere("{$k} != :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'ngt':
                    $builder->andWhere("{$k} >= :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'gt':
                    $builder->andWhere("{$k} > :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'nlt':
                    $builder->andWhere("{$k} <= :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'lt':
                    $builder->andWhere("{$k} < :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                    
                case 'in':
                    $builder->inWhere($k, $v[1]);
                    break;
                    
                case 'like':
                    $builder->andWhere("{$k} like :{$k}:", [
                        $k => $v[1]
                    ]);
                    break;
                default:
                    
                    Util::throwException(1001, 'where is exception');
                    
            } // end swich
        } //
        
        
        
        return $builder;
    }
    
    
    /**
     * 对应的model全路径名
     *
     * @author WYY 2020-01-02 16:42
     * @return mixed
     */
    protected static function getModelClassName()
    {
        $class_name = str_replace([
            'Dao',
            'dao'
        ], [
            'Model',
            'model'
        ], get_called_class());
        
        return $class_name;
    }
    
    
}

