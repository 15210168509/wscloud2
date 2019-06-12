<?php

namespace Lib\TaskQueue\Lib;

/**
 * 消费者队列相关处理
 * User: dbn
 * Date: 2017/9/29
 * Time: 16:25
 */
final class ConsumerList extends Redis
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
     * 处理消费者队列数据，每次请求处理一条数据
     * @param string $key 消费者队列Key
     * @return string 'null'||'success'||'fail'
     * null：主队列中没有数据，需要延迟执行下次请求
     * success/fail：处理成功/失败，立即执行下次请求
     */
    public function dataProcessing($key)
    {

        // 从消费队列中弹出一条数据
        $data = $this->getListData($key);
        if (empty($data)) {
            return 'null';
        }
        $dataArr = json_decode($data, true);

        // 获取全部Handle
        $handle = $this->getDataHandle($dataArr);
        if (is_array($handle) && !empty($handle)) {

            // 处理Handle
            $handleInfo = $this->getHandleDistinguish($handle);

            // 验证是否有无效Handle
            if (empty($handleInfo['execVain'])) { // 全部有效

                // 执行Handle
                return $this->hookHandle($handle, $dataArr, $key);

            } else { // 有无效Handle

                // 存在非法Handle，防止Handle依赖，不对此数据进行处理，记录结果，直接删除
                Log::INFO('[' . $key . '] 数据中删除存在非法Handle：' . $data . ' - 非法Handle：' . json_encode($handleInfo['execVain']));
                $this->setTaskResult($dataArr['taskId'], TaskQueueConfig::TASK_RESULT_FAIL, '数据中删除存在非法Handle：'. json_encode($handleInfo['execVain']));
                return 'fail';
            }
        } else {

            // 未获取到Handle，删除数据，记录结果，不进行处理。
            Log::INFO('[' . $key . '] 删除未获取到Handle数据：' . $data);
            $this->setTaskResult($dataArr['taskId'], TaskQueueConfig::TASK_RESULT_FAIL, '未获取到Handle');
            return 'fail';
        }
    }

    /**
     * 判断数据执行次数是否达到限制
     * @param  int $num 执行次数
     * @return boolean  超出返回false，未超出返回true
     */
    private function isDataExecNum($num)
    {
        if ($num >= TaskQueueConfig::REDIS_TASK_EXEC_MAX) {
            return false;
        }
        return true;
    }

    /**
     * 获取数据中全部Handle
     * @param  array $data 数据
     * @return array
     */
    private function getDataHandle($data)
    {
        $handle = $data['extend'];

        // 获取话题
        $topicConfig = TaskQueueConfig::$_topic;
        $isTopic     = array_key_exists($data['topic'], $topicConfig);
        if ($isTopic) {
            $handle  = array_merge($handle, $topicConfig[$data['topic']]);
        }
        return $handle;
    }

    /**
     * 获取Handle中有效Handle和无效Handle
     * @param  array $handle 所有Handle
     * @return array
     */
    private function getHandleDistinguish($handle)
    {
        $execValid = array(); // 有效Handle
        $execVain  = array(); // 无效Handle
        foreach($handle as $val) {
            $class = 'Lib\TaskQueue\Handle\\' . $val . 'Handle';
            if(method_exists($class, 'run')) {
                $execValid[] = $val;
            } else {
                $execVain[]  = $val;
            }
        }
        return array(
            'execValid' => $execValid,
            'execVain'  => $execVain
        );
    }

    /**
     * 执行Handle
     * @param  array   $handle  有效的Handle
     * @param  array   $data    Redis数据
     * @param  string  $listKey 队列Key
     * @return boolean
     */
    private function hookHandle($handle, $data, $listKey)
    {
        $taskId      = $data['taskId'];
        $param       = $data['data'];

        $execSuccess = array(); // 成功执行Handle
        $execError   = $handle; // 未成功执行Handle

        // 不是第一次执行，则执行以前失败的Handle，已成功的Handle不执行
        if (is_numeric($data['execNum']) && intval($data['execNum']) > 0) {
            $execSuccess = isset($data['execInfo']['execSuccess']) ? $data['execInfo']['execSuccess'] : array(); // 成功执行Handle
            $execError   = isset($data['execInfo']['execError']) ? $data['execInfo']['execError'] : array();     // 未成功执行Handle
        }

        // 执行未成功的Handle
        if (is_array($execError) && !empty($execError)) {

            foreach ($execError as $key => $val) {
                $class = 'Lib\TaskQueue\Handle\\' . $val . 'Handle';
                $result = call_user_func_array(array($class, 'run'),  array($param));

                // 验证执行结果
                if ($result) {
                    $execSuccess[] = $val;
                    unset($execError[$key]);
                } else {

                    // 不继续向下执行，记录检查点，下次继续执行
                    break;
                }
            }
        }

        // 判断结果，记录日志
        if (empty(array_diff($handle, $execSuccess))) {

            // 全部执行成功，记录执行结果
            Log::INFO('[' .$listKey . '] [' . $taskId . '] 执行成功【执行Handle：'.json_encode($handle).'】');
            $this->setTaskResult($taskId, TaskQueueConfig::TASK_RESULT_SUCCESS, '执行成功【执行Handle：'.json_encode($handle).'】');
            return 'success';
        }

        // 未全部执行成功，执行失败。记录执行信息
        $newExecNum       = $data['execNum'] + 1;
        $isNewExecNum     = $this->isDataExecNum($newExecNum);
        $data['execNum']  = $newExecNum;
        Log::INFO('[' .$listKey . '] [' . $taskId . '] 执行失败【执行成功Handle：'.json_encode($execSuccess).' - 未成功执行Handle：'.json_encode($execError).'】');

        if ($isNewExecNum) {

            // 执行次数没达到上限，不记录结果，重新推到主队列，重新分配执行。
            $data['execInfo'] = array(
                'execSuccess' => $execSuccess,
                'execError'   => $execError
            );
            Log::INFO('[' .$listKey . '] [' . $taskId . '] 执行失败，重新推送数据到主队列：' . json_encode($data));
            $this->pushList(TaskQueueConfig::REDIS_TASK_MAIN_LIST, json_encode($data));
        } else {

            // 未全部执行成功，执行次数达到上限，删除数据，调用回调，记录结果。
            if (is_array($execError) && !empty($execError)) {
                foreach ($execError as $key => $val) {
                    $class = 'Lib\TaskQueue\Handle\\' . $val . 'Handle';
                    call_user_func_array(array($class, 'errorCallbackRun'),  array($param));
                }
            }
            if (is_array($execSuccess) && !empty($execSuccess)) {
                foreach ($execSuccess as $key => $val) {
                    $class = 'Lib\TaskQueue\Handle\\' . $val . 'Handle';
                    call_user_func_array(array($class, 'successCallbackRun'),  array($param));
                }
            }

            Log::INFO('[' .$listKey . '] [' . $taskId . '] 删除执行次数达到最大数据：' . json_encode($data));
            $this->setTaskResult($taskId, TaskQueueConfig::TASK_RESULT_FAIL, '执行失败【执行成功Handle：'.json_encode($execSuccess).' - 未成功执行Handle：'.json_encode($execError).'】');
        }
        return 'fail';
    }
}