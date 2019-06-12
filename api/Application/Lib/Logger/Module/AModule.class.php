<?php

namespace Lib\Logger\Module;

/**
 * 模块抽象类
 * User: dbn
 * Date: 2018/5/30
 * Time: 10:37
 */
abstract class AModule
{
    /**
     * 日志处理实例
     * @var $logger
     */
    protected $logger;

    /**
     * 当前类实例
     * @var $_instance
     */
    protected static $_instance = null;

    /**
     * 获取类实例
     * @return null|object
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            $prodClass = new \ReflectionClass(get_called_class());
            self::$_instance = $prodClass->newInstance();
        }
        return self::$_instance;
    }

    /**
     * 将日志携带数据转换为json格式
     * @param $data
     * @return string
     */
    protected function data2json($data)
    {
        $data = is_array($data)
            ? json_encode($data)
            : (
            empty($data)
                ? '{}'
                : (json_decode($data) ? $data : '{}')
            );
        return $data;
    }

    /**
     * 写入DEBUG级别日志
     * @param string $message 日志信息
     * @param array/string $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    abstract public function debug($message, $data=array(), $topic='');

    /**
     * 写入INFO级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    abstract public function info($message, $data=array(), $topic='');

    /**
     * 写入WARN级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    abstract public function warn($message, $data=array(), $topic='');

    /**
     * 写入ERROR级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    abstract public function error($message, $data=array(), $topic='');

    /**
     * 写入FATAL级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    abstract public function fatal($message, $data=array(), $topic='');

    /**
     * 查询日志
     * @param int $startTime 查询起始时间，时间戳
     * @param int $endTime 查询结束时间，时间戳
     * @param string $topic 日志主题，默认''
     * @param string $query 查询语句, 默认''，查询语句需要配置相应的全文索引（只能登录阿里配置），参考阿里日志服务文档
     * @param int $line 查询日志返回条数，默认返回50条
     * @param int $offset 查询日志返回偏移量，默认0
     * @param int $reverse 0||1 是否反向返回，如果将反向设置为1，则查询将首先返回最新的日志。默认0
     * @return mixed
     */
     public function getLogs($startTime, $endTime, $topic = '', $query = '', $line = 50, $offset = 0, $reverse = 0)
     {
         return array();
     }
}