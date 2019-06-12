<?php

namespace Lib;

/**
 * Redis数据维护类
 * @author dbn
 */
class RedisData{

    public static $redisLock = null;

    public static function getRedisLock(array $options = array()){
        if(!self::$redisLock){
            self::$redisLock = self::createRedisLock($options);
        }
        return self::$redisLock;
    }

    public static function getRedis()
    {
        $redisLock = self::getRedisLock();
        return $redisLock->getRedis();
    }

    protected static function createRedisLock(array $options = array()){

        $context = RedisLock::getInstance($options);

        return $context;
    }

    /**
     * Redis 键/值 数据存储/读取通用设置
     * @param  string      $type  前缀，主类别（车辆或者司机等）
     * @param  string      $id    信息唯一标识
     * @param  string      $field 后缀，自定的key
     * @param  string      $value 保存的值
     * @return string/bool 如果$value不为空设置key返回设置结果，如果为空返回key对应的值
     */
    public static function redisAttr($type, $id, $field, $value='') {
        $redis_key = $type . '_' . $id . '_' . $field;
        if ($value == '') {
            return S($redis_key);
        }
        $res = S($redis_key, $value);
        return $res;
    }

    /**
     * Redis 键/值 数据删除通用设置
     * @param  string      $type  前缀，主类别（车辆或者司机等）
     * @param  string      $id    信息唯一标识
     * @param  string      $field 后缀，自定的key
     * @return bool 返回结果
     */
    public static function redisRemove($type, $id, $field) {
        $redis_key = $type . '_' . $id . '_' . $field;
        return S($redis_key, null);
    }

    /**
     * Redis 哈希表 数据存储/读取通用设置
     * @param  string $key   哈希表的key
     * @param  string $field 字段
     * @param  mixed  $value 数据
     * @return mixed 如果$value不为空返回设置结果，如果为空返回字段对应的值
     */
    public static function redisHashAttr($key, $field, $value='')
    {
        $redis = self::getRedis();
        if ($value == '') {
            $res = $redis->hget($key, $field);
            if (!empty($res)) {
                $res = unserialize($res);
            }
            return $res;
        }
        // 判断哈希表字段是否存在
        $is_exists = $redis->hexists($key, $field);
        $res = $redis->hset($key, $field, serialize($value));
        if ($is_exists == 0) {
            return $res;
        }
        return 1;
    }

    /**
     * Redis 哈希表 数据删除通用设置
     * @param  string $key   哈希表的key
     * @param  string $field 字段（不存在的字段将被忽略）
     * @return int 成功删除的数量
     */
    public static function redisHashRemove($key, $field)
    {
        $redis = self::getRedis();
        return $redis->hdel($key, $field);
    }

    /**
     * Redis 哈希表 数据清空通用设置
     * @param  string $key   哈希表的key
     * @return int 成功1，失败0
     */
    public static function redisHashFlushAll($key)
    {
        $redis = self::getRedis();
        $keys = $redis->hkeys($key);
        if (is_array($keys) && count($keys) > 0) {
            foreach ($keys as $val) {
                self::redisHashRemove($key, $val);
            }
        }
        if (false !== $keys) return 1;
        return 0;
    }

    /**
     * Redis 集合 数据存储通用设置
     * @param  string $key    集合的key
     * @param  string $member 成员
     * @return int
     */
    public static function redisSetAttr($key, $member)
    {
        $redis = self::getRedis();
        return $redis->sadd($key, $member);
    }

    /**
     * Redis 集合 数据集合转移通用设置
     * @param  string $source      源集合key
     * @param  string $destination 目标集合key
     * @param  string $member      需要转移的成员
     * @return int
     */
    public static function redisSetMove($source, $destination, $member)
    {
        $redis = self::getRedis();
        return $redis->smove($source, $destination, $member);
    }

    /**
     * Redis 集合 数据删除通用设置
     * @param  string $key    集合的key
     * @param  string $member 需要删除的成员
     * @return int
     */
    public static function redisSetRemove($key, $member)
    {
        $redis = self::getRedis();
        return $redis->srem($key, $member);
    }

    /**
     * Redis 数据自增通用设置
     * @param  string $key key
     * @return int 返回自增后的值，如果 key 不存在，那么 key 的值会先被初始化为 0 ，然后再执行 INCR 操作。
     * 如果值包含错误的类型，或字符串类型的值不能表示为数字，那么返回一个错误。
     */
    public static function redisIncr($key)
    {
        $redis = self::getRedis();
        return $redis->incr($key);
    }

    public static function redisIncrBy($key,$num)
    {
        $redis = self::getRedis();
        return $redis->incrBy($key,$num);
    }

    /**
     * Redis 根据str查询
     * @param  string $str 模糊查询key
     * @return array 符合条件的所有key
     */
    public static function redisKeys($str)
    {
        $redis = self::getRedis();
        return $redis->keys($str);
    }

}