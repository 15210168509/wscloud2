<?php

namespace Lib\TaskQueue\Handle;
use Lib\AutoValidation;
use Lib\RedisData;

/**
 * 运输单流量操作
 */

class TransportAmountHandle implements IHandle
{
    public function run($param)
    {
        $validate = array(
            array('companyId', 'require', '公司标识缺失', AutoValidation::MUST_VALIDATE, ''),
            array('companyId', 'number', '公司标识非法', AutoValidation::MUST_VALIDATE, ''),
            array('companyId', '10', '公司标识非法', AutoValidation::MUST_VALIDATE, 'length'),
            array('companyType', 'require', '下单类型缺失', AutoValidation::MUST_VALIDATE, ''),
            array('companyType', 'number', '下单类型非法', AutoValidation::MUST_VALIDATE, ''),
            array('companyType', '1,2', '下单类型非法', AutoValidation::MUST_VALIDATE, 'length'),
        );

        $autoValidate = new AutoValidation($validate);
        $validateResult = $autoValidate->validation($param);

        if ($validateResult) {
            $key = 'transportStatAmount_' . date('Y-m-d') . '_' . $param['companyId'] . '_' . $param['companyType'];
            $result = RedisData::redisIncr($key);
            if (false !== $result && $result > 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
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