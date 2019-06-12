<?php

namespace Lib\TaskQueue\Handle;
use Lib\AutoValidation;
use Lib\RedisData;
use Lib\Tools;

/**
 * 生成订单运输合同
 */
class CreateTranContractHandle implements IHandle
{
    public function run($param)
    {
        $validate = array(
            array('orderId', 'require', '订单ID缺失', AutoValidation::MUST_VALIDATE, ''),
            array('orderId', 'number', '订单ID非法', AutoValidation::MUST_VALIDATE, ''),
            array('orderId', '10', '订单ID非法', AutoValidation::MUST_VALIDATE, 'length'),
            array('acceptId', 'require', '承运人ID缺失', AutoValidation::MUST_VALIDATE, ''),
            array('acceptId', 'number', '承运人ID非法', AutoValidation::MUST_VALIDATE, ''),
            array('acceptId', '10', '承运人ID非法', AutoValidation::MUST_VALIDATE, 'length'),
            array('acceptSource', 'require', '承运人来源缺失', AutoValidation::MUST_VALIDATE, ''),
            array('acceptSource', 'number', '承运人来源非法', AutoValidation::MUST_VALIDATE, ''),
            array('dest', array('D', 'F', 'S', 'E'), '输出方式非法', AutoValidation::EXISTS_VALIDATE, 'in'),
            array('ssl', array(true, false), '安全设置非法', AutoValidation::EXISTS_VALIDATE, 'in'),
        );

        $autoValidate = new AutoValidation($validate);
        $validateResult = $autoValidate->validation($param);

        if ($validateResult) {
            if (!isset($param['dest'])) $param['dest'] = 'F';
            if (!isset($param['ssl'])) $param['ssl'] = false;
            return Tools::createOrderTransportContract($param['orderId'], $param['acceptId'], $param['acceptSource'], $param['dest'], $param['ssl']);
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