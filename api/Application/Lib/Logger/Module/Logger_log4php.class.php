<?php

namespace Lib\Logger\Module;

/**
 * 使用log4php处理日志
 * User: dbn
 * Date: 2018/5/30
 * Time: 10:04
 */
class Logger_log4php extends AModule
{
    /**
     * Logger_log4php constructor.
     */
    public function __construct()
    {
        vendor('log4php.Logger');
        \Logger::configure(VENDOR_PATH.'/log4php/conf/log4php.xml');
        $this->logger = \Logger::getRootLogger();
    }

    /**
     * 写入DEBUG级别日志
     * @param string $message 日志信息
     * @param array/string $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function debug($message, $data=array(), $topic='')
    {
        $this->logger->debug($this->params2json($message, $data, $topic));
    }

    /**
     * 写入INFO级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function info($message, $data=array(), $topic='')
    {
        $this->logger->info($this->params2json($message, $data, $topic));
    }

    /**
     * 写入WARN级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function warn($message, $data=array(), $topic='')
    {
        $this->logger->warn($this->params2json($message, $data, $topic));
    }

    /**
     * 写入ERROR级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function error($message, $data=array(), $topic='')
    {
        $this->logger->error($this->params2json($message, $data, $topic));
    }

    /**
     * 写入FATAL级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function fatal($message, $data=array(), $topic='')
    {
        $this->logger->fatal($this->params2json($message, $data, $topic));
    }

    /**
     * 将参数转换为一个json字符串
     */
    private function params2json($message, $data, $topic)
    {
        return json_encode(array(
            'message' => strval($message),
            'data'    => $this->data2json($data),
            'topic'   => strval($topic)
        ));
    }
}