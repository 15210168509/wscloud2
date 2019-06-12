<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/11/27
 * Time: 10:04
 */

namespace Lib;


class RedisTest
{
    private $redis;
    protected static $instance;
    private function __construct($options = array())
    {
        //默认配置
        $defaultOptions = array(
            'host' => '192.168.1.104',
            'port' => 6379
        );
        //合并配置
        if(is_array($options)&&!empty($options)){
            $defaultOptions = array_merge($defaultOptions,$options);
        }
        $this->redis = new \Redis();
        $this->redis->connect($defaultOptions['host'],$defaultOptions['port']);
    }

    private function __clone(){}

    public static function getInstance($options=array())
    {
        if (!isset(self::$instance))
            self::$instance = new RedisTest($options);
        return self::$instance;
    }

    /*************redis字符串操作命令*****************/

    /**
     * 设置一个key
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function set($key,$value)
    {
        return $this->redis->set($key,$value);
    }

    /**
     * 得到一个key
     * @param string $key
     * @return boolean
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 设置一个有过期时间的key
     * @param string $key
     * @param int $expire 秒
     * @param string $value
     * @return boolean
     */
    public function setex($key,$expire,$value)
    {
        return $this->redis->setex($key,$expire,$value);
    }


    /**
     * 设置一个key,如果key存在,不做任何操作并返回false
     * @param string $key
     * @param string $value
     * @return boolean
     */
    public function setnx($key,$value)
    {
        return $this->redis->setnx($key,$value);
    }

    /**
     * 批量设置key
     * @param array $arr
     * @return boolean
     */
    public function mset($arr)
    {
        return $this->redis->mset($arr);
    }

    /**
     * 加锁
     * @param $key
     * @param int $time
     * @return bool
     */
    public function lock($key,$time=5)
    {
        $flg = $this->redis->setnx($key,time()+$time);
        if (!$flg) {
            $value = $this->redis->get($key);
            if (time()>$value) {
                //锁过期，解锁重新设置
                $this->unlock($key);
                $flg = $this->redis->setnx($key,time()+$time);
            }
        }
        return $flg;
    }

    /**
     * 解锁
     * @param $key
     * @return bool
     */
    public function unlock($key)
    {
        return $this->del($key);
    }

    /*********************队列操作命令************************/

    /**
     * 在队列尾部插入一个元素
     * @param string $key
     * @param string $value
     * @return int 返回队列长度
     */
    public function rPush($key,$value)
    {
        return $this->redis->rPush($key,$value);
    }

    /**
     * 在队列尾部插入一个元素 如果key不存在，什么也不做
     * @param string $key
     * @param string $value
     * @return int 返回队列长度
     */
    public function rPushx($key,$value)
    {
        return $this->redis->rPushx($key,$value);
    }

    /**
     * 在队列头部插入一个元素
     * @param string $key
     * @param string $value
     * @return int 返回队列长度
     */
    public function lPush($key,$value)
    {
        return $this->redis->lPush($key,$value);
    }

    /**
     * 在队列头插入一个元素 如果key不存在，什么也不做
     * @param string $key
     * @param string $value
     * @return int 返回队列长度
     */
    public function lPushx($key,$value)
    {
        return $this->redis->lPushx($key,$value);
    }

    /**
     * 返回队列长度
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 返回队列指定区间的元素
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array 包含$start,$end对应值
     */
    public function lRange($key,$start,$end)
    {
        return $this->redis->lrange($key,$start,$end);
    }

    /**
     * 返回队列中指定索引的元素
     * @param string $key
     * @param int $index
     * @return string|false
     */
    public function lIndex($key,$index)
    {
        return $this->redis->lIndex($key,$index);
    }

    /**
     * 设定队列中指定index的值。
     * @param string $key
     * @param int $index
     * @param string $value
     * @return boolean
     */
    public function lSet($key,$index,$value)
    {
        return $this->redis->lSet($key,$index,$value);
    }

    /**
     * 删除值为vaule的count的绝对值个元素
     * count>0 从尾部开始
     *  <0　从头部开始
     *  =0　删除全部
     * @param string $key
     * @param int $count
     * @param string $value
     * @return int 返回删除的个数
     */
    public function lRem($key,$count,$value)
    {
        return $this->redis->lRem($key,$value,$count);
    }

    /**
     * 删除并返回队列中的头元素。
     * @param string $key
     * @return string|false
     */
    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     * 删除并返回队列中的尾元素
     * @param string $key
     * @return string|false
     */
    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    /*****************hash表操作函数*******************/

    /**
     * 得到hash表中一个字段的值
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return string|false
     */
    public function hGet($key,$field)
    {
        return $this->redis->hGet($key,$field);
    }

    /**
     * 为hash表设定一个字段的值
     * @param string $key 缓存key
     * @param string  $field 字段
     * @param string $value 值。
     * @return bool
     */
    public function hSet($key,$field,$value)
    {
        return $this->redis->hSet($key,$field,$value);
    }

    /**
     * 判断hash表中，指定field是不是存在
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return bool
     */
    public function hExists($key,$field)
    {
        return $this->redis->hExists($key,$field);
    }

    /**
     * 删除hash表中指定字段 ,支持批量删除
     * @param string $key 缓存key
     * @param string  $field 字段
     * @return int
     */
    public function hdel($key,$field)
    {
        $fieldArr=explode(',',$field);
        $delNum=0;

        foreach($fieldArr as $row)
        {
            $row=trim($row);
            $delNum+=$this->redis->hDel($key,$row);
        }

        return $delNum;
    }

    /**
     * 返回hash表元素个数
     * @param string $key 缓存key
     * @return int|bool
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 为hash表设定一个字段的值,如果字段存在，返回false
     * @param string $key 缓存key
     * @param string  $field 字段
     * @param string $value 值。
     * @return bool
     */
    public function hSetNx($key,$field,$value)
    {
        return $this->redis->hSetNx($key,$field,$value);
    }

    /**
     * 为hash表多个字段设定值。
     * @param string $key
     * @param array $value
     * @return array|bool
     */
    public function hMset($key,$value)
    {
        if(!is_array($value))
            return false;
        return $this->redis->hMset($key,$value);
    }

    /**
     * 为hash表多个字段设定值。
     * @param string $key
     * @param array|string $value string以','号分隔字段
     * @return array|bool
     */
    public function hMget($key,$field)
    {
        if(!is_array($field))
            $field=explode(',', $field);
        return $this->redis->hMget($key,$field);
    }

    /**
     * 为hash表设这累加，可以负数
     * @param string $key
     * @param int $field
     * @param string $value
     * @return int
     */
    public function hIncrBy($key,$field,$value)
    {
        $value=intval($value);
        return $this->redis->hIncrBy($key,$field,$value);
    }

    /**
     * 返回所有hash表的所有字段
     * @param string $key
     * @return array|bool
     */
    public function hKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 返回所有hash表的字段值，为一个索引数组
     * @param string $key
     * @return array|bool
     */
    public function hVals($key)
    {
        return $this->redis->hVals($key);
    }

    /**
     * 返回所有hash表的字段值，为一个关联数组
     * @param string $key
     * @return array
     */
    public function hGetAll($key)
    {
        return $this->redis->hGetAll($key);
    }

    /*********************有序集合操作*********************/

    /**
     * 给当前集合添加一个元素
     * 如果value已经存在，会更新order的值。
     * @param string $key
     * @param string $order 序号
     * @param string $value 值
     * @return bool
     */
    public function zAdd($key,$order,$value)
    {
        return $this->redis->zAdd($key,$order,$value);
    }

    /**
     * 给$value成员的order值，增加$num,可以为负数
     * @param string $key
     * @param string $num 序号
     * @param string $value 值
     * @return int 返回新的order
     */
    public function zincrby($key,$num,$value)
    {
        return $this->redis->zincrby($key,$num,$value);
    }

    /**
     * 删除值为value的元素
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function zRem($key,$value)
    {
        return $this->redis->zRem($key,$value);
    }

    /**
     * 集合以order递增排列后，0表示第一个元素，-1表示最后一个元素
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array|bool
     */
    public function zRange($key,$start,$end)
    {
        return $this->redis->zRange($key,$start,$end);
    }

    /**
     * 集合以order递减排列后，0表示第一个元素，-1表示最后一个元素
     * @param string $key
     * @param int $start
     * @param int $end
     * @return array|bool
     */
    public function zRevRange($key,$start,$end)
    {
        return $this->redis->zRevRange($key,$start,$end);
    }

    /**
     * 集合以order递增排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     * @param string $key
     * @param int $start
     * @param int $end
     * @package array $option 参数
     *     withscores=>true，表示数组下标为Order值，默认返回索引数组
     *     limit=>array(0,1) 表示从0开始，取一条记录。
     * @return array|bool
     */
    public function zRangeByScore($key,$start='-inf',$end="+inf",$option=array())
    {
        return $this->redis->zRangeByScore($key,$start,$end,$option);
    }

    /**
     * 集合以order递减排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     * @param string $key
     * @param int $start
     * @param int $end
     * @package array $option 参数
     *     withscores=>true，表示数组下标为Order值，默认返回索引数组
     *     limit=>array(0,1) 表示从0开始，取一条记录。
     * @return array|bool
     */
    public function zRevRangeByScore($key,$start='-inf',$end="+inf",$option=array())
    {
        return $this->redis->zRevRangeByScore($key,$start,$end,$option);
    }

    /**
     * 返回order值在start end之间的数量
     * @param string $key
     * @param string $start
     * @param string $end
     * @return boolean
     */
    public function zCount($key,$start,$end)
    {
        return $this->redis->zCount($key,$start,$end);
    }

    /**
     * 返回值为value的order值
     * @param string $key
     * @param string $value
     * @return float
     */
    public function zScore($key,$value)
    {
        return $this->redis->zScore($key,$value);
    }

    /**
     * 返回集合以score递增排序后，指定成员的排序号，从0开始。
     * @param string $key
     * @param string $value
     * @return int
     */
    public function zRank($key,$value)
    {
        return $this->redis->zRank($key,$value);
    }

    /**
     * 返回集合以score递减排序后，指定成员的排序号，从0开始。
     * @param string $key
     * @param string $value
     * @return int
     */
    public function zRevRank($key,$value)
    {
        return $this->redis->zRevRank($key,$value);
    }

    /**
     * 删除集合中，score值在start end之间的元素　包括start end
     * min和max可以是-inf和+inf　表示最大值，最小值
     * @param string $key
     * @param int $start
     * @param int $end
     * @return 删除成员的数量。
     */
    public function zRemRangeByScore($key,$start,$end)
    {
        return $this->redis->zRemRangeByScore($key,$start,$end);
    }

    /**
     * 返回集合元素个数。
     * @param string $key
     * @return int
     */
    public function zCard($key)
    {
        return $this->redis->zCard($key);
    }

    /*************redis　无序集合操作命令*****************/

    /**
     * 返回集合中所有元素
     * @param string $key
     * @return array
     */
    public function sMembers($key)
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 求2个集合的差集
     * @param string $key1
     * @param string $key2
     * @return array 返回 key1与key2的差值
     */
    public function sDiff($key1,$key2)
    {
        return $this->redis->sDiff($key1,$key2);
    }

    /**
     * 集合中添加成员，不能重复
     * @param string $key
     * @param string|$value
     * @return int
     */
    public function sAdd($key,$value)
    {
        return $this->redis->sAdd($key,$value);
    }

    /**
     * 返回无序集合的元素个数
     * @param string $key
     * @return int
     */
    public function scard($key)
    {
        return $this->redis->scard($key);
    }

    /**
     * 从集合中删除一个元素
     * @param string $key
     * @param string $value
     * @return int
     */
    public function srem($key,$value)
    {
        return $this->redis->srem($key,$value);
    }

    /*************redis管理操作命令*****************/

    /**
     * 返回key,支持*多个字符，?一个字符
     * 只有*　表示全部
     * @param string $key
     * @return array
     */
    public function keys($key)
    {
        return $this->redis->keys($key);
    }

    /**
     * 删除指定key
     * @param string $key
     * @return boolean
     */
    public function del($key)
    {
        $res = $this->redis->del($key);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断一个key值是不是存在
     * @param string $key
     * @return boolean
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 为一个key设定过期时间 单位为秒
     * @param string $key
     * @param int $expire
     * @return boolean
     */
    public function expire($key,$expire)
    {
        return $this->redis->expire($key,$expire);
    }

    /**
     * 返回一个key还有多久过期，单位秒
     * @param string $key
     * @return int 剩余秒数，过期返回-1
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    /**
     * 设定一个key什么时候过期，time为一个时间戳
     * @param string $key
     * @param string $time
     * @return boolean
     */
    public function exprieAt($key,$time)
    {
        return $this->redis->expireAt($key,$time);
    }


    /**
     * 返回一个随机key
     */
    public function randomKey()
    {
        return $this->redis->randomKey();
    }

}