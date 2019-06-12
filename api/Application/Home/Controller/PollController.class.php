<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/23
 * Time: 9:54
 */

namespace Home\Controller;


use Lib\Code;
use Lib\CommonConst;
use Lib\Msg;
use Lib\RedisLock;
use Lib\StatusCode;
use Lib\Tools;
use Lib\Ws\WsClient;

class PollController extends AdvancedRestController
{
    /**
     * 车辆位置信息
     * author 李文起
     * @param $data
     */
    private function updatePosition($data){
        $redis = RedisLock::getInstance();
        foreach ($data as $value){
            $redis->set($value['device_no'],json_encode($value),0);
            $this->setReturnVal(Code::OK, Msg::ADD_SUCCESS, StatusCode::ADD_SUCCESS);
        }
    }

    /**
     * 批量上传上传
     * author 李文起
     */
    public function uploadTiredValue(){
        $res = WsClient::getInstance()->tiredValuePoll();
        if ($res['code'] == Code::OK) {
            if ($res['code'] == Code::OK && count($res['data']['position_info']) > 0) {
                $this->updatePosition($res['data']['position_info']);
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING, StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }
}