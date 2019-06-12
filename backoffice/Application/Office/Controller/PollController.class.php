<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/22
 * Time: 18:50
 */

namespace Office\Controller;


use Lib\Code;
use Lib\ListManagementController;
use Lib\Ws\WsClient;

class PollController extends ListManagementController
{
    public function tiredValuePoll(){
        $res = WsClient::getInstance()->tiredValuePoll();

        if ($res['code'] == Code::OK && count($res['data']) > 0){
            $model = D('Poll');
            $res = $model->uploadTiredValue($res['data']);
            return $res;
        }
    }
}