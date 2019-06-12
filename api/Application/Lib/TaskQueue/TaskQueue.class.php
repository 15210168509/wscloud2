<?php

namespace Lib\TaskQueue;

use Lib\HObject;
use Lib\TaskQueue\Lib\CLogFileHandler;
use Lib\TaskQueue\Lib\Log;
use Lib\TaskQueue\Lib\MainList;

/**
 * 任务队列相关操作
 * User: dbn
 * Date: 2017/9/28
 * Time: 16:54
 */

class TaskQueue extends HObject
{

    // 主队列处理对象
    private $_mainList;

    public function __construct()
    {
        $this->_mainList = MainList::getInstance();

        // 初始化日志
        $logHandler = new CLogFileHandler(__DIR__ . '/Logs/'.date('y-m-d').'.log');
        Log::Init($logHandler, 15);
    }


    /**
     * 添加任务
     * @param  string  $topic  话题
     * @param  array   $data   数据
     * @param  array   $extend  扩展Handle [handle1, handle2, ...]
     * @return string  任务ID，添加不成功时返回''
     */
    public function addTaskQueue($topic, $data, $extend = array())
    {
        if (!empty($topic) && is_array($data) && is_array($extend)) {
            $taskId = $this->getTaskUniqueId();
            $arr = array(
                'taskId' => $taskId,
                'topic'  => $topic,
                'data'   => $data,
                'extend' => $extend
            );

            $result = $this->_mainList->pushMainList($arr);

            if (false !== $result && $result > 0) {
                return $taskId;
            } else {
                $this->setError('添加任务失败');
                return '';
            }
        }
        $this->setError('参数错误');
        return '';
    }

    /**
     * 查询任务执行结果
     * @param  string $taskId 任务ID
     * @return array  [code=>0||1||2, msg=>msg]
     */
    public function getTaskExecResult($taskId)
    {
        return $this->_mainList->getTaskExecResult($taskId);
    }

    /**
     * 生成任务ID
     */
    private function getTaskUniqueId()
    {

        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < 10; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        $time = microtime(true);
        $unique_id = str_shuffle(str_replace('.', '', $time));
        $arr = str_split($unique_id);
        shuffle($arr);
        while (count($arr) > 9) {
            $index = mt_rand(0, count($arr)-1);
            unset($arr[$index]);
        }
        array_unshift($arr, mt_rand(1, 9));
        $unique_id = implode('', $arr);

        return 'TaskQueue_' . $str . $unique_id;
    }
}