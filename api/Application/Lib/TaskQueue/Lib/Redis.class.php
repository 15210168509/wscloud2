<?php
/**
 * Redis 相关操作
 * User: dbn
 * Date: 2017/9/29
 * Time: 11:34
 */

namespace Lib\TaskQueue\Lib;

use Lib\RedisLock;

class Redis
{

    /**
     * 获取Redis对象
     */
    private function getRedis()
    {
        $redisLock = RedisLock::getInstance();
        return $redisLock->getRedis();
    }

    /**
     * 向队列中添加数据
     * @param  string $key 队列Key
     * @param  string $value 数据，以JSON形式保存
     * @return mixed  如果成功返回队列中数据的总条数(>0)，如果失败返回false
     */
    protected function pushList($key, $value)
    {
        $redis  = $this->getRedis();
        $result = $redis->rpush($key, $value);
        if (false !== $result) {
            Log::INFO('[' . $key . '] ' . $value);
        }
        return $result;
    }

    /**
     * 从队列中弹出一条数据
     * @param  string $key 队列Key
     * @return string 如果成功返回数据，如果队列为空或者失败返回''
     */
    protected function getListData($key)
    {
        $data = $this->popList($key);
        if (false == $data) {
            return '';
        }
        $dataArr = json_decode($data, true);

        // 验证数据
        $isParam = $this->checkParam($data);
        if (!$isParam) {

            // 非法的数据，直接从队列中删除，记录结果。重新获取一条新的数据
            Log::INFO($key . '中删除非法数据：' . $data);
            if (isset($dataArr['taskId'])) {
                $this->setTaskResult($dataArr['taskId'], TaskQueueConfig::TASK_RESULT_FAIL, '数据非法');
            }
            return $this->getListData($key);
        }

        return $data;
    }

    /**
     * 记录执行结果
     * @param string $taskId 任务ID
     * @param int    $code   成功1，失败0
     * @param string $msg    信息
     */
    protected function setTaskResult($taskId, $code, $msg)
    {
        $redis   = $this->getRedis();
        $seconds = TaskQueueConfig::REDIS_TASK_RESULT_TIME * 60;
        $data    = json_encode(array('code' => $code, 'msg' => $msg));
        return $redis->setex($taskId, $seconds, $data);
    }

    /**
     * 查询任务执行结果
     * @param  string $taskId 任务ID
     * @return mixed  成功返回数据，失败返回false
     */
    public function getTaskResult($taskId)
    {
        $redis  = $this->getRedis();
        return $redis->get($taskId);
    }

    /**
     * 从队列中弹出一条数据
     * @param  string $key 队列Key
     * @return mixed 如果成功返回数据，如果队列为空或者失败返回false
     */
    private function popList($key)
    {
        $redis  = $this->getRedis();
        return $redis->lpop($key);
    }

    /**
     * 验证数据
     * @param  array   $data Redis取出的数据
     * @return boolean
     */
    private function checkParam($data)
    {
        $data = json_decode($data, true);
        if (is_array($data) && isset($data['taskId']) && isset($data['topic']) && isset($data['data'])
            && is_array($data['data']) && isset($data['extend']) && is_array($data['extend'])
        ) {
            return true;
        }
        return false;
    }
}