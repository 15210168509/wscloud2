<?php

namespace Lib\Logger\Module;

/**
 * 使用logCenter处理日志
 * User: dbn
 * Date: 2018/5/30
 * Time: 10:04
 */
class Logger_logCenter extends AModule
{
    /**
     * Logger_logCenter constructor.
     */
    public function __construct()
    {
        vendor('LogCenter.LogCenter');
        $this->logger = \LogCenter::getInstance();
    }

    /**
     * 写入DEBUG级别日志
     * @param string $message 日志信息
     * @param array/string $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function debug($message, $data=array(), $topic='')
    {
        $this->logger->debug($message, $data, $topic);
    }

    /**
     * 写入INFO级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function info($message, $data=array(), $topic='')
    {
        $this->logger->info($message, $data, $topic);
    }

    /**
     * 写入WARN级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function warn($message, $data=array(), $topic='')
    {
        $this->logger->warn($message, $data, $topic);
    }

    /**
     * 写入ERROR级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function error($message, $data=array(), $topic='')
    {
        $this->logger->error($message, $data, $topic);
    }

    /**
     * 写入FATAL级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function fatal($message, $data=array(), $topic='')
    {
        $this->logger->fatal($message, $data, $topic);
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
        return $this->logger->getLogs($startTime, $endTime, $topic, $query, $line, $offset, $reverse);
    }
}