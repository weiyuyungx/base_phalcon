<?php
namespace app\libary;

/**
 * 工具类（通用）
 *
 * @author Administrator
 */
class Util
{

    /**
     * post请求
     *
     * @author WYY 2018年7月31日 上午9:44:30
     * @param string $url
     * @param array $post_data
     * @return mixed
     */
    static function curl_post($url, $post_data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, ($post_data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 这个是重点。 不验证证书

        $result = curl_exec($curl);

        if (curl_errno($curl))
        {
            return curl_error($curl);
        }

        curl_close($curl);

        return $result;
    }

    /**
     * post 请求JSON
     *
     * @author WYY 2018年12月3日 上午9:34:36
     * @param string $url
     * @param array $post_data
     */
    static function curl_post_json($url, $json_data)
    {
        // json也可以
        if (is_string($json_data))
            $data_string = $json_data;
        else
            $data_string = json_encode($json_data, JSON_UNESCAPED_SLASHES);

        // curl验证成功
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string)
        ));

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 这个是重点。 不验证证书

        $result = curl_exec($ch);
        if (curl_errno($ch))
        {
            return curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    /**
     * 从二维数组中提供某个键的值(去重复)
     *
     * @author WYY 2018年8月17日 上午9:32:44
     * @param array|object $arr
     * @param string $key
     */
    static function getValusFromArray($arr, $key)
    {
        $new_data = [];
        foreach ($arr as $k => $v)
        {
            if (is_array($v))
                $new_data[] = $v[$key];
            elseif ($v instanceof \Phalcon\Mvc\Model)
                $new_data[] = $v->getAttr($key);
            elseif (is_object($v))
                $new_data[] = $v->$key;
        }

        $new_data = array_unique($new_data);

        return array_values($new_data);
    }

    /**
     * 大写命名换成下划线
     *
     * @author WYY 2018年9月12日 上午11:52:47
     * @param string $name
     * @return string
     * @example OrgModel -> org_model
     */
    static function cc_format($name)
    {
        $temp_array = array();
        for ($i = 0; $i < strlen($name); $i ++)
        {
            $ascii_code = ord($name[$i]);
            if ($ascii_code >= 65 && $ascii_code <= 90)
            {
                if ($i == 0)
                {
                    $temp_array[] = chr($ascii_code + 32);
                } else
                {
                    $temp_array[] = '_' . chr($ascii_code + 32);
                }
            } else
            {
                $temp_array[] = $name[$i];
            }
        }
        return implode('', $temp_array);
    }

    
    /** 
     * #下划线转驼峰
     * @author  WYY 2020-06-17 16:08
     * @param string $str
     * @param boolean $ucfirst
     * @return string
     */
    static function convertUnderline( $str , $ucfirst = true)
    {
        while(($pos = strpos($str , '_'))!==false)
            $str = substr($str , 0 , $pos).ucfirst(substr($str , $pos+1));
            
        return $ucfirst ? ucfirst($str) : $str;
    }
    
    
    /**
     * 获取IP
     *
     * @author WYY 2018年11月7日 下午2:28:28
     * @return string|boolean|mixed
     */
    static function getIp()
    {
        $HTTP_CLIENT_IP = isset($_SERVER['HTTP_CLIENT_IP'])? $_SERVER['HTTP_CLIENT_IP'] : '';
        
        $HTTP_X_FORWARDED_FOR = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
        
        $REMOTE_ADDR = isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '';
        
        
        if ($HTTP_CLIENT_IP && strcasecmp($HTTP_CLIENT_IP, "unknown"))
        {
            $ip = $HTTP_CLIENT_IP;
        } else if ($HTTP_X_FORWARDED_FOR && strcasecmp($HTTP_X_FORWARDED_FOR, "unknown"))
        {
            $ip = $HTTP_X_FORWARDED_FOR;
        } 
        else if ($REMOTE_ADDR && strcasecmp($REMOTE_ADDR, "unknown"))
        {
            $ip = $REMOTE_ADDR;
        } 
        else if (isset($REMOTE_ADDR) && $REMOTE_ADDR && strcasecmp($REMOTE_ADDR, "unknown"))
        {
            $ip = $REMOTE_ADDR;
        } else
        {
            $ip = "unknown";
        }
        return $ip;
    }

    /**
     * 获取真实IP（代理服务器IP）
     *
     * @author WYY 2019年4月29日 下午5:08:55
     * @return string|boolean|mixed
     */
    static function get_real_ip()
    {
        $ip = FALSE;

        // 客户端IP 或 NONE

        if (! empty($_SERVER["HTTP_CLIENT_IP"]))
        {

            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }

        // 多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空

        if (! empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {

            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);

            if ($ip)
            {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }

            for ($i = 0; $i < count($ips); $i ++)
            {

                if (! eregi("^(10│172.16│192.168).", $ips[$i]))
                {

                    $ip = $ips[$i];

                    break;
                }
            }
        }

        // 客户端IP 或 (最后一个)代理服务器 IP

        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    /**
     * 精确到小数点四位的时间戳
     *
     * @author WYY 2018年11月9日 上午10:32:50
     * @return number
     */
    static function microtime_float()
    {
        list ($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * 毫秒时间戳
     *
     * @author WYY 2020年2月24日 下午5:27:24
     * @return number
     */
    static function msectime()
    {
        list ($msec, $sec) = explode(' ', microtime());
        $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 是否为https请求
     *
     * @author WYY 2018年12月24日 下午3:43:25
     * @return boolean
     */
    static function is_https()
    {
        if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        {
            return true;
        } elseif (! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        {
            return true;
        }
        return false;
    }

    /**
     * 是否微信访问
     *
     * @author WYY 2019年2月12日 下午12:03:34
     * @return boolean
     */
    static function isWeixin()
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false)
        {
            return true;
        }
        return false;
    }

    /**
     * 当前请求的URL全路径
     *
     * @author WYY 2019年2月20日 上午10:31:13
     * @example http://192.168.2.224/cocbs20/phalcon/public/index.php?_url=/organize/manage.Act/actAttaList/
     */
    static function getCurFullUrl()
    {
        if (self::is_https())
        {
            $h = 'https://';
        } else
        {
            $h = 'http://';
        }

        return $h . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
    }

    /**
     * 当前url请求的文件 不含参数
     *
     * @author WYY 2019年2月20日 上午10:43:10
     * @example http://192.168.2.224:80/cocbs20/phalcon/public/index.php
     */
    static function getCurQuestFile()
    {
        if (self::is_https())
        {
            $h = 'https://';
        } else
        {
            $h = 'http://';
        }

        return $h . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
    }

    static function gethost()
    {
        if (self::is_https())
        {
            $h = 'https://';
        } else
        {
            $h = 'http://';
        }

        return $h . $_SERVER['SERVER_NAME'];
    }

    /**
     * 抛出异常
     *
     * @author WYY 2020-01-07 10:30
     * @param
     *            $code
     * @param
     *            $msg
     * @throws \Exception
     */
    static function throwException($code, $msg)
    {
        throw new \Exception($msg, $code);
    }
    
    
    
    /** 
     * 获取的DI
     * @author WYY  2019-12-02 09:46
     */
    static function getDi()
    {
        return  \Phalcon\Di::getDefault();
    }
    
    
    /**
     * #数据库连接
     * @author  WYY 2020-04-28 10:01
     * @return \Phalcon\Db\Adapter\Pdo\Mysql
     */
    static function getConnect()
    {
        return self::getDi()->get('db');
    }
    
    
    
    /** 获取session
     * @author WYY  2019-12-05 16:36
     * @return \Phalcon\Session\Adapter\Files
     */
    static function getSession()
    {
        $di = self::getDi();
        
        return $di->get('session');
    }
    
    
    
    /** 注解器
     * @author  WYY 2020年2月27日 下午5:16:44
     * @return \Phalcon\Annotations\Adapter\Memory
     */
    static function getAnnotations()
    {
        return self::getDi()->get('annotations');
    }
    

    /**
     * #获取一个Transaction
     * @author  WYY 2020-05-14 11:54
     * @return \Phalcon\Mvc\Model\Transaction
     */
    public static function getTransaction()
    {

        return self::getDi()->get('transactionManager')->get();
    }
    
    
    /** 
     * #获取配置信息
     * @author  WYY 2020-06-19 09:25
     * @return \Phalcon\Config\Adapter\Ini
     */
    public static function getConfig() 
    {       
        static $config;
        if (empty($config))
        {
            $config = new \Phalcon\Config\Adapter\Ini(BASE_DIR.'/config.ini');
            
            $env_file = BASE_DIR.'/my.ini';
            if (is_file($env_file))
            {
                $env = new \Phalcon\Config\Adapter\Ini($env_file);
                
                //合并两份配置
                $config->merge($env);
            }

        }

        return $config;
    } 
    
    
    /**
     * #获取缓存实例
     * @author WYY 2018年9月5日 上午10:27:18
     * @return \Phalcon\Cache\Backend\File
     */
    public static function getCache()
    {
       return self::getDi()->get('modelsCache');
    }
    
    /** 
     * #获取modelsManager
     * @author  WYY 2020-06-19 15:49
     * @return \Phalcon\Mvc\Model\Manager
     */
    public static function getModelsManager() 
    {
        return self::getDi()->get('modelsManager');
    }
    
    /** 
     * #尝试结束请求求
     * <li>php-fpm下有效
     * <li>返回数据给客户端后，程序依然往下走
     * @author  WYY 2020-07-30 17:00
     */
    public static function tryEndRequest()
    {
        if (function_exists('fastcgi_finisth_request'))
        {
            fastcgi_finisth_request();
        }
    }
    
    
    
}