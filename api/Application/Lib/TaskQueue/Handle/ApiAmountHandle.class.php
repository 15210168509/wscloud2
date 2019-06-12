<?php

namespace Lib\TaskQueue\Handle;
use Lib\AutoValidation;
use Lib\RedisData;

/**
 * 订单流量操作
 */

class ApiAmountHandle implements IHandle
{
    public function run($param)
    {

        $key = 'apiAmount_' . date('Y-m-d') . '_' . $param['corpId'] . '_' . $param['apiName'];
        $result = RedisData::redisIncr($key);
        if (false !== $result && $result > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function errorCallbackRun($param)
    {
        // TODO: Implement errorCallbackRun() method.
    }

    public function successCallbackRun($param)
    {
        // TODO: Implement successCallbackRun() method.
    }
}