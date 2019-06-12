<?php
/**
 * Redis锁相关，内部接口不对外调用
 * Created by dbn.
 * Date: 2017/2/8
 * Time: 11:13
 */

namespace Lib;

class RedisLock
{
    private $_redis;

    /**
     * RedisLock 实体
     */
    protected static $instance;

    public function __construct($options = array())
    {
        //默认配置
        $defaultOptions = array(
            'type' => 'redis',
            'host' => C('REDIS_HOST'),
            'port' => C('REDIS_PORT')
        );
        //合并配置
        if(is_array($options)&&!empty($options)){
            $defaultOptions = array_merge($defaultOptions,$options);
        }

        $this->_redis = S($defaultOptions);
    }

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean 如果锁不存在或者过期返回true，并将锁重新设置。如果锁存在并且不过期返回false
     */
    public function lock($key, $expire=5){

        $is_lock = $this->_redis->setnx($key, time()+$expire);

        if(!$is_lock){
            // 判断锁是否过期
            $lock_time = $this->_redis->get($key);
            // 锁已过期，删除锁，重新设置
            if(time() > $lock_time){
                $this->unlock($key);
                $is_lock = $this->_redis->setnx($key, time()+$expire);
            }
        }

        return $is_lock ? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key){
        return $this->_redis->del($key);
    }

    public static function getInstance($options=array())
    {
        if (!isset(self::$instance))
            self::$instance = new RedisLock($options);
        return self::$instance;
    }

    public function getRedis()
    {
        return $this->_redis;
    }

    public function Llen($key){
        return $this->_redis->Llen($key);
    }

    public function delete($key){
        return $this->_redis->del($key);
    }

    public function Rpush($key,$value){

        return $this->_redis->RPUSH($key,serialize($value));
    }
    public function Lpop($key){
        return $this->_redis->Lpop($key);
    }
    public function Lindex($key,$index){
        if($result = $this->_redis->Lindex($key,$index)){
            return unserialize($result);
        }
        return false;
    }

    /**
     * 添加元素到有序集合
     * @param  string $key   有序集合key
     * @param  int    $score 权重
     * @param  string $data  值
     * @return int 操作结果
     */
    public function zadd($key, $score, $data)
    {
        return $this->_redis->zadd($key, $score, $data);
    }

    /**
     * 删除元素到有序集合
     * @param  string $key  有序集合key
     * @param  string $data 值
     * @return int 操作结果
     */
    public function zrem($key, $data)
    {
        return $this->_redis->zrem($key, $data);
    }

    /**
     * 获取有序集合的长度
     * @param  string $key 有序集合key
     * @return int 操作结果
     */
    public function zcard($key)
    {
        return $this->_redis->zcard($key);
    }

    /**
     * 更改有序集合元素的权重
     * @param  string $key      有序集合key
     * @param  int    $newScore 更改权重的值（累计修改，正数基础上增加，负数基础上减少）
     * @param  string $data     值
     * @return int 操作结果
     */
    public function zincrby($key, $newScore, $data)
    {
        return $this->_redis->zincrby($key, $newScore, $data);
    }

    /**
     * 获取有序集合元素的权重
     * @param  string $key  有序集合key
     * @param  string $data 值
     * @return int 操作结果
     */
    public function zscore($key, $data)
    {
        return $this->_redis->zscore($key, $data);
    }

    /**
     * 获取有序集合的元素
     * @param  string $key  有序集合key
     * @param  int    $min  起始索引
     * @param  int    $max  结束索引
     * @return int 操作结果
     */
    public function zrange($key, $min=0, $max=0)
    {
        return $this->_redis->zrange($key, $min, $max);
    }

    /**
     * 延长过期时间
     */
    public function expire($key, $second)
    {
        return $this->_redis->EXPIRE($key, $second);
    }

    /**
     * 添加元素
     * @param $key
     * @return mixed
     */
    public function get($key){
        return $this->_redis->get($key);
    }

    /**
     * 设置元素
     * @param $key
     * @return mixed
     */
    public function set($key,$value,$expire=60){
        return $this->_redis->set($key,$value,$expire);
    }

    /**
     * 删除redis的元素
     * @param $key
     * @return mixed
     */
    public function rm($key){
        return $this->_redis->rm($key);
    }

    /**
     * 加载lua脚本
     * author 李文起
     * @param $command
     * @param $lua
     * @return mixed
     */
    public function script($command,$lua){
        return $this->_redis->script($command,$lua);
    }

    /**
     * 执行脚本
     * author 李文起
     * @param $hash
     * @param $args
     * @param int $num
     * @return mixed
     */
    public function evalSha($hash,$args,$num = 1){
        return $this->_redis->evalSha($hash,$args,$num);
    }
}