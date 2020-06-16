<?php
namespace app\base;

use app\libary\Util;


/**
 *
 * @author Administrator
 *        
 */
abstract class BaseModel extends \Phalcon\Mvc\Model
{

    const MAX_INT = 2147483647;

    const TEXT_LENGTH = 65535;

    /**
     * 数据表前缀
     *
     * @var string
     */
    protected static $db_pre = 'bh_';



    /**
     * 构造
     *
     * @author WYY 2019-11-27 16:52
     */
    public function onConstruct()
    {
        $this->useDynamicUpdate(true); // 动态更新

//         self::setup([
//             'virtualForeignKeys' => TRUE
//         ]);

        
        // 设置表前缀
        $called_class = get_called_class();
        
        $class = substr($called_class, strrpos($called_class, '\\') + 1);
        
        $base_class = str_replace('Model', '', $class);
        
        $this->setSource(static::$db_pre . Util::cc_format($base_class));
    }

    /**
     * get方法
     *
     * @author WYY 2020年3月11日 下午4:20:17
     * @param string $field
     * @return string
     */
    public function getAttr($field)
    {
        return $this->readAttribute($field);
    }

    /**
     * set方法
     *
     * @author WYY 2020年3月11日 下午4:20:32
     * @param string $field
     * @param string $value
     * @return
     */
    public function setAttr($field, $value)
    {
        return $this->writeAttribute($field, $value);
    }

    /**
     * 添加一条(简单)错误信息
     *
     * @author WYY 2020年3月12日 下午3:34:08
     * @param string $msg
     */
    public function addErrorMsg($msg)
    {
        $this->appendMessage(new \Phalcon\Mvc\Model\Message($msg));
    }

    /**
     * 抛出msg
     *
     * @author WYY 2020年3月16日 上午11:37:46
     * @throws \Phalcon\Exception
     */
    public function outputErrorMsg($code ,$msg = 'fails')
    {

        
        $msgs = $this->getMessages();
        
        if (count($msgs))
        {
            $str = '';
            foreach ($msgs as $message)
            {
                $str .= $message . "###";
            }
            
            throw new \Phalcon\Exception($msg.':' . $str, $code);
        }
    }

    /**
     * *********************以下为事件触发函数,已经按先后排序,子类重写*******************************
     */

    
    
    /**
     * #准备save(优先级最高)
     * <li>对数据的预处理，如关系模型
     * @author WYY 2018年11月6日 下午2:41:31
     */
    public function prepareSave() 
    {
        
    }
    
    
    /**
     * #验证前
     * @author WYY 2018年11月6日 下午2:41:31
     */
    public function beforeValidation()
    {

    }
    
    /**
     * (Insert)验证数据之前
     *
     * @author WYY 2018年10月29日 上午10:38:30
     * @example 填充字段默认值
     */
    public function beforeValidationOnCreate()
    {}

    /**
     * #修改时验证数据
     *
     * @author WYY 2018年11月6日 下午2:41:31
     */
    public function beforeValidationOnUpdate()
    {
    
    }

    /**
     * #验证数据
     * <li>从子类获取字段注解验证
     * <li>这里为一级验证（数据库级 ,大小，长度，类型）
     * <li>return false 时触发onValidationFails 不再往下触发</li>
     * <li>return 其它时 往下触发 </li>
     *
     * @author WYY 2018年10月29日 上午11:25:34
     */
    public function validation()
    {
        $validate = new \Phalcon\Validation();
        $adapter = Util::getAnnotations();

        $reflector = $adapter->get(static::class);

        $annotations = $reflector->getPropertiesAnnotations();

        foreach ($annotations as $field => $one)
        {
            //ID不做限制
            if (! $one->has('Column') || $field == 'id')
            {
                continue;
            }

            /**
             *
             * @var \Phalcon\Annotations\Annotation $arg
             */
            $arg = $one->get('Column');

            $type = $arg->getNamedArgument('type');

            $options = []; // 初始化
                            // 根据类型验证
            if ($type == 'string' or $type == 'varchar')
            {

                $length = $arg->getNamedArgument('length');

                $length_min = 0;
                if ($arg->hasArgument('min'))
                    $length_min = (int) $arg->getNamedArgument('min');

                $options['min'] = [
                    $field => $length_min
                ];
                $options['max'] = [
                    $field => $length
                ];

                // $options['min'] = $v[1];
                // $options['max'] = $v[2];

                if ($arg->hasArgument('errmsg'))
                {
                    $options['messageMinimum'] = [
                        $field => $arg->getNamedArgument('errmsg')
                    ];
                    $options['messageMaximum'] = [
                        $field => $arg->getNamedArgument('errmsg')
                    ];
                }

                $validator = new \Phalcon\Validation\Validator\StringLength($options);
                $validate->add($field, $validator);
            } elseif ($type == 'integer')
            {

                if (! (is_numeric($this->getAttr($field)) && strpos($this->getAttr($field), '.') === false))
                    throw new \Phalcon\Exception($field . '必须是整数', 94);

                if ($arg->hasArgument('in'))  //如果指定in  @example in="1,2,3"
                {
                    
                    $options['domain'] = explode(',', $arg->getNamedArgument('in'));;
                    
                    if ($arg->hasArgument('errmsg'))
                    {
                        $options['message'] = [
                            $field => $arg->hasArgument('errmsg')
                        ];
                    }
                    
                    $validator = new \Phalcon\Validation\Validator\InclusionIn($options);
                    
                } else  //大小限制
                {
                    $options['minimum'] = - 1 * (self::MAX_INT + 1);
                    $options['maximum'] = self::MAX_INT;

                    if ($arg->hasArgument('max'))
                        $options['maximum'] = $arg->getNamedArgument('max');

                    if ($arg->hasArgument('min'))
                        $options['minimum'] = $arg->getNamedArgument('min');

                    if ($arg->hasArgument('errmsg'))
                    {
                        $options['message'] = [
                            $field => $arg->hasArgument('errmsg')
                        ];
                    }

                    $validator = new \Phalcon\Validation\Validator\Between($options);
                }

                $validate->add($field, $validator);
                
                
            } elseif ($type == 'text')
            {
                $length = self::TEXT_LENGTH;

                $length_min = 0;
                if ($arg->hasArgument('min'))
                    $length_min = (int) $arg->getNamedArgument('min');

                $options['min'] = [
                    $field => $length_min
                ];
                $options['max'] = [
                    $field => $length
                ];

                // $options['min'] = $v[1];
                // $options['max'] = $v[2];

                if ($arg->hasArgument('errmsg'))
                {
                    $options['messageMinimum'] = [
                        $field => $arg->getNamedArgument('errmsg')
                    ];
                    $options['messageMaximum'] = [
                        $field => $arg->getNamedArgument('errmsg')
                    ];
                }

                $validator = new \Phalcon\Validation\Validator\StringLength($options);
                $validate->add($field, $validator);
            } elseif ($type == 'decimal')
            {

                if ($arg->hasArgument('max'))
                    $options['maximum'] = $arg->getNamedArgument('max');

                if ($arg->hasArgument('min'))
                    $options['minimum'] = $arg->getNamedArgument('min');

                $validator = new \Phalcon\Validation\Validator\Numericality($options);

                $validate->add($field, $validator);
            }
        } // end foreach

        return $this->validate($validate);
    }

    // end

    
    /**
     * #验证失败时
     * <li>validation()返回false时触发
     * @author WYY 2020年3月16日 上午11:40:49
     * @throws \Phalcon\Exception
     */
    public function onValidationFails()
    {

        $this->outputErrorMsg(91,'ValidationFails');
    }

    
    /** 
     * #验证数据后(更新时)
     * <li>二级验证 (如数据格式)
     * @author  WYY 2020-05-27 16:57
     */
    public function afterValidationOnUpdate()
    {
        
    }
    
    /**
     * #验证数据后(插入时时)
     * <li>二级验证 (如数据格式)
     * @author  WYY 2020-05-27 16:57
     */
    public function afterValidationOnCreate()
    {
        
    }
    
    
    /**
     * #验证数据后
     * <li>二级数据验证(比较字段大小,字段互斥,数据格式等)
     * <li>业务级的验证
     * @author WYY 2019年2月21日 上午9:43:45
     * @example 开始时间/结束时间比较
     */
    public function afterValidation()
    {
        
    }

    
    /**
     * #保存前的操作
     *
     * @author WYY 2018年10月29日 下午4:28:24
     */
    public function beforeSave()
    {
        $this->outputErrorMsg(92,'beforeSave');
    }


    /**
     * (Insert)数据前的操作
     *
     * @author WYY 2018年10月29日 下午3:07:55
     * @example 将数据哈希再入库
     * @example 唯一值判断
     */
    public function beforeCreate()
    {}

    /**
     * (Update)数据前的操作
     *
     * @author WYY 2018年10月29日 下午3:07:55
     * @example 将数据哈希再入库
     * @example 唯一值判断
     */
    public function beforeUpdate()
    {}



    /**
     * 新增完成后
     * <li>save保存后触发
     * @author WYY 2019-10-08 17:26
     */
    public function afterCreate()
    {}

    /**
     * 更新完成后
     * <li>save保存后触发
     * @author WYY 2019-10-08 17:27
     */
    public function afterUpdate()
    {
        
    }

    
    /**
     * 保存失败时
     *<li>save之前不拦截,已经执行save()但返回false
     *<li> save()已经执行 === false时触发
     * @author WYY 2019-09-23 11:11
     */
    public function notSaved()
    {
        //为了防止save之前有message,但又不拦截(return false)
        $this->outputErrorMsg(93,'notSaved');
    }
    
    
    /**
     * 保存后处理的数据
     * <li>只要正常流程（不拦截），就能到这步
     * <li>save()已经执行  !== false时触发
     * @author WYY 2018年10月29日 下午2:44:35
     * @throws \Phalcon\Exception
     */
    public function afterSave()
    {
        //为了防止save之前有message,但又不拦截(return false)
        $this->outputErrorMsg(94,'afterSave');
    }


    /**
     * 删除之前
     * 子类重写该方法，
     * 明写return false来阻止删除
     *
     * @author WYY 2018年11月28日 上午10:18:03
     */
    public function beforeDelete()
    {}

    /**
     * 删除的钩子
     *
     * @author WYY 2019-08-30 09:39
     * {@inheritdoc}
     * @see \Phalcon\Mvc\Model::delete()
     */
    public function delete()
    {
        $result = parent::delete();

        // 删除失败的时候
        if ($result == false)
        {
            $this->outputErrorMsg(95,'deleteFail');
        }

        return $result;
    }

    /**
     * 删除之后
     * <li>删除成功才执行</li>
     *
     * @author WYY 2019年2月21日 下午2:35:02
     */
    public function afterDelete()
    {}

    /**
     * ******************************工具函数************************************
     */

    /**
     * 雪花算法生成ID
     *
     * @author WYY 2020年2月24日 下午5:06:44
     */
    public static function SnowFlake()
    {
        // 机器号
        $machine_num = 0; // 5位 0-31

        $time = Util::msectime(); // 41位

        // 随机数 0 - (2^18-1)
        $rand = rand(0, pow(2, 18) - 1);
    }
}

