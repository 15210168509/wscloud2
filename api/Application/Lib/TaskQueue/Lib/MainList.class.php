<?php

namespace Lib\TaskQueue\Lib;

/**
 * 主队列相关处理
 * User: dbn
 * Date: 2017/9/29
 * Time: 11:28
 */

final class MainList extends Redis
{

    private static $instance;
    private function __clone(){}
    private function __construct(){}

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
     * 向主队列添加任务
     * @param  array $data 数据
     * @return mixed  如果成功返回队列中数据的总条数(>0)，如果失败返回false
     */
    public function pushMainList($data)
    {
        return $this->pushList(TaskQueueConfig::REDIS_TASK_MAIN_LIST, json_encode($data));
    }

    /**
     * 主队列任务分发
     * @return string 'null'||'success'||'fail'
     * null：主队列中没有数据，需要延迟执行下次请求
     * success/fail：处理成功/失败，立即执行下次请求
     */
    public function dataDistribution()
    {

        // 从主队列中获取一条数据
        $data = $this->getListData(TaskQueueConfig::REDIS_TASK_MAIN_LIST);
        if (empty($data)) {
            return 'null';
        }
        $dataArr = json_decode($data, true);

        // 使用哈希算法获取分配的队列
        $consistentHash = ConsistentHash::getInstance();
        $server = $consistentHash->getSaveServer($dataArr['taskId']);
        if (empty($server)) {

            // 获取消费者队列失败，将数据重新保存到主队列中
            $this->pushMainList($dataArr);
            Log::ERROR('获取消费者队列失败：'.$data);
            return 'fail';
        }

        // 判断是否是新任务，初始化执行数据
        if (!isset($dataArr['execNum'])) {
            $dataArr['execNum']  = 0;       // 执行次数
            $dataArr['execInfo'] = array(); // 执行信息
        }

        // 分发数据
        $result = $this->pushList($server, json_encode($dataArr));

        if (false !== $result && $result > 0) {

            // 分发数据成功
            return 'success';
        } else {

            // 分发数据失败，将数据重新保存到主队列中
            $this->pushMainList($dataArr);
            Log::ERROR('分发数据失败：'.json_encode($dataArr));
        }

        return 'fail';
    }

    /**
     * 查询任务执行结果
     * @param  string $taskId 任务ID
     * @return array  [code=>0||1||2, msg=>msg]
     */
    public function getTaskExecResult($taskId)
    {
        $result = $this->getTaskResult($taskId);
        if (!$result) {
            return array('code' => TaskQueueConfig::TASK_RESULT_NULL, 'msg' => '未查询到结果');
        }
        return $result;
    }
}