<?php
namespace app\model;


/**
 * 用户的model
 * <li>数据表示层，要做的事情就是把数据表示清楚，完整
 * <li>model代表数据库里的一条记录
 * <li>不允许在本类里做SQL相关。SQL是DAO层做的事。
 * <li>model属于数据表示层，只能做数据表示的事，不要瞎管其它的事。
 * <li>如把未完整的数据表示完整，或者对数据格式的判断，或者对数据大小互斥的判断
 * <li>用注解来区分数据库字段/自定义属性。并在注解里做格式大小限制。保存里自动验证
 * <li>全部的注解，可以根据数据库内容，用工具生成。免手写(可以手工小修改)
 */
class UserModel extends \app\base\BaseModel
{

    /**
     * @Primary
     * @Identity
     * @Column(type='integer', nullable=false,skip_on_insert)
     */
    public $id;

    /**
     * 用户名
     * <li>保存时，会自动验证注解里的限制
     * @Column(type='string', length=32, nullable=false, min=3 ,errmsg='用户名长度3,32')
     */
    public $name;

    /**
     * 手机号
     * @Column(type='string', length=16, nullable=false)
     */
    public $mobile;

    /**
     * 微信unionid
     * @Column(type='string', length=32, nullable=false, min=4 ,errmsg="unionid长度4-32")
     */
    public $unionid;

    /**
     * 创建时间
     * @Column(type='integer', nullable=false ,min=1)
     */
    public $createtime;

    /**
     * 性别
     * <li>0,未知，1男，2女。
     * <li>用注解限制其取值范围。好处是IDE可以弹出帮助查看
     * @Column(type='integer', nullable=false ,in='0,1,2')
     */
    public $sex;
    
    /**
     * #性别（未知）
     * <li>常量的前缀必须与对应的字段相同
     * @var integer
     */
    const SEX_UNKNOW = 0;

    /**
     * #性别（男）
     * @var integer
     */
    const SEX_M = 1;

    /**
     * #性别(女)
     * @var integer
     */
    const SEX_F = 2;

    /**
     * 头像文件
     * <li>存在数据库里的头像一般为 xxx.jpg ，无路径，只有文件名
     * <li>需要把该文件死还原成有效的路径
     * @Column(type='string', length=32, nullable=false ,errmsg="最长32字符")
     */
    public $headimg;

    
    /**
     * #密码
     * <li>注意这里限制的是原始密码
     * <li>所以入库之前需要做hash
     * <li>密码的哈希等操作在本层处理完成。对上层是无感的
     * @Column(type='string', length=32, min=6 , nullable=false ,errmsg="密码最短6位")
     */
    public $passwd;
    
    
    /**
     * #盐
     * <li>盐是自动生成，上层调用应该是无感
     * @Column(type='string', length=6, min=6 ,nullable=false ,errmsg="salt必须6位")
     */
    public $salt;
    
    
    /**
     * #自定义的属性（没有注解）
     * <li>并非数据库的字段
     * <li>不受model事件，过滤等影响
     * <li>如在save()时，会被过滤掉.不会入库的
     * <li>不会出现在toArray()里
     * @var string
     */
    public $myxx;


    /****************************钩子事件，自动触发************************************/
    
    
    
    
    
    
    /**
     * #验证前(新增)
     * {@inheritDoc}
     * @see \app\base\BaseModel::beforeValidationOnCreate()
     * @author WYY 2020-06-16 11:14
     */
    public function beforeValidationOnCreate()
    {
        // TODO Auto-generated method stub
        parent::beforeValidationOnCreate();

        //自动生成时间
        $this->createtime = time();
        
        //自动生成盐
        $this->salt = self::getNewSalt();
    }
    
    /**
     * #验证前(修改)
     * {@inheritDoc}
     * @see \app\base\BaseModel::afterValidationOnUpdate()
     * @author WYY 2020-06-16 11:21
     */
    public function beforeValidationOnUpdate()
    {
        // TODO Auto-generated method stub
        parent::beforeValidationOnUpdate();
        
        //如果某些东西一旦生成就不允许修改,或者是自动生成的，不允许直接传值修改
        //那就在这里进行监视数据的改动
        
        if ($this->hasChanged('salt'))
        {
            //salt不允许直接传值修改。只能由系统自动生成
            $this->addErrorMsg('不允许修改salt');
        }


        //如果监视到密码有改变。则连盐也一起改
        if ($this->hasChanged('passwd'))
        {
            $this->salt = self::getNewSalt();
        }
        
    }
    
    /**
     * 二级验证
     * {@inheritDoc}
     * @see \app\base\BaseModel::afterValidation()
     * @author WYY 2020-06-16 14:50
     */
    public function afterValidation()
    {
        // TODO Auto-generated method stub
        parent::afterValidation();

        
        //在这里做二级验证    
        if (!self::isMobile($this->mobile))
        {
            $this->addErrorMsg('手机号格式不对');
        }
    }
    

    
    /**
     * 新增之前
     * {@inheritDoc}
     * @see \app\base\BaseModel::beforeCreate()
     * @author WYY 2020-06-16 11:16
     */
    public function beforeCreate()
    {
        // TODO Auto-generated method stub
       parent::beforeCreate();
       
       //将密码HASH    
       $this->passwd = self::getHashPasswd($this->passwd, $this->salt);    
    }
    
    

    
    /**
     * #修改之前
     * {@inheritDoc}
     * @see \app\base\BaseModel::beforeUpdate()
     * @author WYY 2020-06-16 11:21
     */
    public function beforeUpdate()
    {
        // TODO Auto-generated method stub
        parent::beforeUpdate();
        
        //如果监视到密码有修改，则重新hash密码再入库
        if ($this->hasChanged('passwd'))
        {
            $this->passwd = self::getHashPasswd($this->passwd, $this->salt); 
        }
    }
    
    
    /************************普通辅助功能的方法*************************************/
    

    /**
     * #获取性别的中文String值
     * @author WYY 2020-06-16 09:40
     * @return string
     */
    public function getSexName()
    {
        if ($this->sex == self::SEX_UNKNOW)
        {
            return '未知';
        } elseif ($this->sex == self::SEX_M)
        {
            return '男';
        } elseif ($this->sex == self::SEX_F)
        {
            return '女';
        } else
        {
            return '非法值';
        }
    }

    /**
     * #图片的真实路径
     * <li>用来处理图片的。比如删除
     * @author WYY 2020-06-16 09:44
     * @return string
     */
    public function getHeadimgPath()
    {
        /*
         * 获取图片的真实路径。
         * 不要在前端直接拼接写死。原因如下
         * 1,路径有可能移动的.
         * 2,路径的前缀可能不是死的。对于大量文件的目录里。路径会取hash值的前几位生成
         *   比如 C://xxxx/img/headimg/af/d3/xxx.jpg  af/d3 就是xxx.jpg的hash值
         */
        return 'C://xxxx/xxx/' . $this->headimg;
    }

    /**
     * #获取图片的URL路径
     * @author WYY 2020-06-16 09:51
     * @return string
     */
    public function getHeadimgUrl()
    {
        /*
         * 不要前端直接拼接URL。原因同上
         * 数据表示层就是要把数据表示清楚
         */
        return 'http://xxxx.com/xxx/' . $this->headimg;
    }

    
    /** 
     * #是否为该用户的密码
     * <li>用来登录判断，修改密码判断等
     * @author  WYY 2020-06-16 11:34
     * @param string $passwd
     * @return boolean
     */
    public function isRealPasswd($passwd) 
    {
        $new_passwd = self::getHashPasswd($passwd, $this->salt);
        
        return hash_equals($new_passwd,$this->passwd);
    }
    
    
    /*******************************以下静态方法，归所有类所共有,(言外之意上面的是对象方法，归本对象所有)*********************************/
    
    /**
     * 是否手机号
     * <li>手机号是userModel的一部份。所以userModel判断手机号合法性
     * @author WYY 2020-01-07 10:47
     * @param string $mobile
     */
    public static function isMobile($mobile)
    {
        $result = preg_match('/^(1[3-9])\d{9}$/', $mobile);

        return $result;
    }
    
    
    /** 
     * #新生成一个盐
     * @author  WYY 2020-06-16 11:12
     * @return number
     */
    public static function getNewSalt()
    {
        //简单随机
        return rand(100000,999999);
    }
    
    
    /** 
     * #将明文密码哈希
     * @author  WYY 2020-06-16 11:19
     * @param string $passwd
     * @param string $salt
     * @return string
     */
    public static function getHashPasswd($passwd , $salt) 
    {
        return md5('xxx_'.$passwd.'_'.$salt);
    }
    
    
    /************************重写的方法***************************/
    
    
    /**
     * #重写方法
     * <li>当本类被转化成array时(如输出JSON)，自动调用方法
     * {@inheritdoc}
     * @see \Phalcon\Mvc\Model::toArray()
     * @author WYY 2020-06-16 09:54
     */
    public function toArray($columns = null)
    {
        // TODO Auto-generated method stub
        /*
         * 此处的toArray只包括数据库里的字段。
         * 不包括自定义的属性。
         */
        
        $data = parent::toArray($columns);

        // 添加上一些常用的数据表示
        $data['sex_name'] = $this->getSexName();
        $data['headimg_url'] = $this->getHeadimgUrl();
        
        //也可以屏蔽一些重要的数据
        unset($data['passwd']);
        unset($data['salt']);

        return $data;
    }

    
    
    
}

