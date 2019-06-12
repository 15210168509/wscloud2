<?php

namespace Lib\TaskQueue;
use Lib\HObject;
use Lib\TaskQueue\Lib\CLogFileHandler;
use Lib\TaskQueue\Lib\ConsumerList;
use Lib\TaskQueue\Lib\Log;
use Lib\TaskQueue\Lib\MainList;

/**
 * 任务队列自动处理程序接口
 * User: dbn
 * Date: 2017/9/29
 * Time: 14:53
 */

final class TaskCron extends HObject
{
    private static $instance;

    // 主队列处理对象
    private $_mainList;

    // 消费者队列处理对象
    private $_consumerList;

    private function __clone(){}
    private function __construct()
    {
        $this->_mainList     = MainList::getInstance();
        $this->_consumerList = ConsumerList::getInstance();

        // 初始化日志
        $logHandler = new CLogFileHandler(__DIR__ . '/Logs/'.date('y-m-d').'.log');
        Log::Init($logHandler, 15);
    }

    /**
     * 获取类的实例化对象
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 主队列任务分发，每次请求处理一条数据
     * @return string 'null'||'success'||'fail'
     * null：主队列中没有数据，需要延迟执行下次请求
     * success/fail：处理成功/失败，立即执行下次请求
     */
    public function cronMainListDataDistribution()
    {
        return $this->_mainList->dataDistribution();
    }

/**
 * ============================================
 *   消费者队列调用程序根据配置文件中配置即时进行调整
 *   队列Key要与配置文件中保持一致
 * ============================================
 */

    /**
     * taskQueueConsumer1：调用程序
     * 每次请求处理一条数据
     * @return string 'null'||'success'||'fail'
     * null：队列中没有数据，需要延迟执行下次请求
     * success/fail：处理成功/失败，立即执行下次请求
     */
    public function cronConsumerList_taskQueueConsumer1()
    {
        return $this->_consumerList->dataProcessing('taskQueueConsumer1');
    }

    /**
     * taskQueueConsumer2：调用程序
     * 每次请求处理一条数据
     * @return string 'null'||'success'||'fail'
     * null：队列中没有数据，需要延迟执行下次请求
     * success/fail：处理成功/失败，立即执行下次请求
     */
    public function cronConsumerList_taskQueueConsumer2()
    {
        return $this->_consumerList->dataProcessing('taskQueueConsumer2');
    }
}