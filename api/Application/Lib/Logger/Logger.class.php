<?php

namespace Lib\Logger;
use Lib\Logger\Module\Logger_log4php;
use Lib\Logger\Module\Logger_logCenter;

/**
 * 日志服务
 * User: dbn
 * Date: 2018/5/30
 * Time: 9:21
 */
class Logger
{
    /**
     * 当前类实例
     * @var $_instance
     */
    private static $_instance = null;

    /**
     * 获取类实例
     * @return null|object
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 获取日志处理对象
     * @return null|object
     */
    private static function getLogger()
    {
        $env = strtolower(C('ENV'));
        switch ($env) {
            case 'release': // 发布，日志存储至日志中心，支持查询
                return Logger_logCenter::getInstance();

            case 'dev' :    // 开发，日志存储在本地，不支持查询，返回array()
            default:
                return Logger_log4php::getInstance();
        }
    }

    /**
     * 写入DEBUG级别日志
     * @param string $message 日志信息
     * @param array/string $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public static function debug($message, $data=array(), $topic='')
    {
        self::getLogger()->debug($message, $data, $topic);
    }

    /**
     * 写入INFO级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public static function info($message, $data=array(), $topic='')
    {
        self::getLogger()->info($message, $data, $topic);
    }

    /**
     * 写入WARN级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public static function warn($message, $data=array(), $topic='')
    {
        self::getLogger()->warn($message, $data, $topic);
    }

    /**
     * 写入ERROR级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public static function error($message, $data=array(), $topic='')
    {
        self::getLogger()->error($message, $data, $topic);
    }

    /**
     * 写入FATAL级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public static function fatal($message, $data=array(), $topic='')
    {
        self::getLogger()->fatal($message, $data, $topic);
    }

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
        return self::getLogger()->getLogs($startTime, $endTime, $topic, $query, $line, $offset, $reverse);
    }
}