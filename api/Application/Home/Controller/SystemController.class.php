<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/12
 * Time: 15:40
 */

namespace Home\Controller;


use Lib\AiConst\BehaviorConst;
use Lib\Code;
use Lib\CommonConst;
use Lib\Msg;
use Lib\RedisLock;
use Lib\StatusCode;
use Lib\Tools;
use Think\Model;

class SystemController extends AdvancedRestController
{

    /**
     * 获取预警类型
     * author 李文起
     */
    public function getWarningType(){
        $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,BehaviorConst::getBehaviorType());
        $this->restReturn();
    }

    /**
     * 获取系统设置
     * author 李文起
     * @param $companyId
     * @param $type
     */
    public function getSystemSetting($companyId,$type){
        $model = D('SystemSetting');

        $map['type']        = $type;
        $map['del_flg']     = CommonConst::DEL_FLG_OK;
        $map['company_id']  = $companyId;

        $res = $model->field('value')->where($map)->find();

        if ($res !== false){
            $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
        }
        $this->restReturn();
    }


    /**
     * 判断设置类型是否已经存在
     * author 李文起
     * @param $companyId
     * @param $type
     * @return bool
     */
    private function checkSettingType($companyId,$type){
        $model = D('SystemSetting');

        $map['company_id']  = $companyId;
        $map['type']         = $type;
        $map['del_flg']      = CommonConst::DEL_FLG_OK;

        $res = $model->where($map)->find();
        if (count($res)>0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 系统类型设置
     * author 李文起
     * @param $adminId
     */
    public function setSystemSetting($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'],true);

        if (is_numeric($param['companyId']) && isset($param['type'])) {

            $model = D('SystemSetting');

            $map['company_id'] = $param['companyId'];
            $map['del_flg']    = CommonConst::DEL_FLG_OK;

            //判断设置是否已经存在
            $res = $this->checkSettingType($param['companyId'],$param['type']);
            //如果存在
            if ($res) {

                $map['type']              = $param['type'];

                $param['update_user']    = $adminId;

                if (!$model->create($param,Model::MODEL_UPDATE)) {
                    $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                } else {
                    $res = $model->where($map)->save();

                    if ($res !== false) {

                        if ($param['type'] == CommonConst::SYSTEM_SET_WARNING) {
                            $redis = RedisLock::getInstance();
                            $redis->set('safe_monitor_type_'.$param['companyId'],$param['value'],0);
                        }

                        $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                    } else {
                        $this->setReturnVal(Code::OK,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                    }

                }
            } else { //如果不存在
                $param['id']             = Tools::generateId();
                $param['company_id']     = $param['companyId'];
                $param['create_user']    = $adminId;
                $param['update_user']    = $adminId;
                if (!$model->create($param,Model::MODEL_INSERT)) {
                    $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                } else {
                    $res = $model->add();

                    if ($res !== false) {

                        if ($param['type'] == CommonConst::SYSTEM_SET_WARNING) {
                            $redis = RedisLock::getInstance();
                            $redis->set('safe_monitor_type_'.$param['companyId'],$param['value'],0);
                        }

                        $this->setReturnVal(Code::OK,Msg::SETTING_SUCCESS,StatusCode::SETTING_SUCCESS);
                    } else {
                        $this->setReturnVal(Code::OK,Msg::SETTING_ERROR,StatusCode::SETTING_ERROR);
                    }

                }
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();

    }
}