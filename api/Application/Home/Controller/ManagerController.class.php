<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/25
 * Time: 16:23
 */

namespace Home\Controller;


use Lib\Code;
use Lib\Msg;
use Lib\Status;
use Lib\StatusCode;
use Think\Model;
use Lib\CommonConst;
use Lib\Tools;
use Lib\SmsService;

class ManagerController extends AdvancedRestController
{
    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 管理员列表数据
     */
    public function managerLists($pageNo, $pageSize,$name = 'null', $phone = 'null', $status = 'null')
    {
        $model = D('Manager');

        $map['del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);
        if (isset($name) && $name != 'null') {
            $map['name'] = array('LIKE', '%'.addslashes($name).'%');
        }
        if (isset($phone) && $phone != 'null') {
            $map['phone'] = array('LIKE', '%'.addslashes($phone).'%');
        }
        if (isset($status) && $status != 'null') {
            $map['status'] = array('EQ', addslashes($status));
        }

        $totalRecord = $model
            ->where($map)
            ->count();

        $num = ceil($totalRecord/$pageSize);
        if ($pageNo > $num) {
            $pageNo = $num;
        }

        $result = $model
            ->field('*')
            ->where($map)
            ->page($pageNo, $pageSize)
            ->order('create_time DESC')
            ->select();

        if (count($result) > 0) {

            foreach ($result as &$value) {
                $value['create_time'] = $value['create_time'] ? date('Y-m-d H:i:s', $value['create_time']) : '无';
                $value['status_name'] = Status::adminStatus2Str($value['status']);
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }


    /**
     * 添加管理员
     */
    public function ajaxAddManager()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            //验证手机号，邮箱，账号是否已存在
            $checkPhone = $this->checkPhone($pare['phone']);
            $checkEmail = $this->checkEmail($pare['email']);
            $checkAccount = $this->checkAccount($pare['account']);
            if (!$checkPhone) {
                $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
            } elseif (!$checkEmail) {
                $this->setReturnVal(Code::ERROR,Msg::EMAIL_EXIST,StatusCode::EMAIL_EXIST);
            } elseif (!$checkAccount) {
                $this->setReturnVal(Code::ERROR,Msg::ACCOUNT_EXIST,StatusCode::ACCOUNT_EXIST);
            } else {
                $pare['create_user'] = $pare['id'];
                $pare['update_user'] = $pare['id'];
                $model = D('Manager');
                if (!$model->create($pare,Model::MODEL_INSERT)) {
                    $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
                } else {
                    $res = $model->add();
                    if ($res) {
                        $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                    } else {
                        $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                    }
                }
            }
        }else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 验证手机号
     * @param $phone
     * @param $ManagerId
     * @return bool
     */
    private function checkPhone($phone,$ManagerId = 'null')
    {
        $model = D('Manager');
        $map['phone'] = $phone;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if ($ManagerId != 'null') {
            $map['id'] = array('NEQ',$ManagerId);
        }

        $res = $model->where($map)->find();
        if ($res) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证邮箱
     * @param $email
     * @param $ManagerId
     * @return bool
     */
    public function checkEmail($email,$ManagerId = 'null')
    {
        $model = D('Manager');
        $map['email'] = $email;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if ($ManagerId != 'null') {
            $map['id'] = array('NEQ',$ManagerId);
        }

        $res = $model->where($map)->find();
        if ($res) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证账号
     * @param $account
     * @param $managerId
     * @return bool
     */
    public function checkAccount($account,$managerId = 'null')
    {
        $model = D('Manager');
        $map['account'] = $account;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        if ($managerId != 'null') {
            $map['id']  = array('NEQ',$managerId);
        }

        $res = $model->where($map)->find();
        if ($res) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 管理员详情
     * @param $manager_id
     */
    public function managerDetail($manager_id)
    {
        if (!empty($manager_id)) {
            $model = D('Manager');
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $map['id'] = $manager_id;
            $res = $model->where($map)->find();
            if ($res) {
                $res['right'] = explode(',',$res['right']);
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::OK,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 修改管理员
     */
    public function ajaxEditManager()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {

            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            $model = D('Manager');
            $where['account'] = $pare['account'];
            $where['phone'] = $pare['phone'];
            $where['email'] = $pare['email'];
            $where['_logic'] = 'or';
            $ac_map['_complex'] = $where;
            $ac_map['id'] = array('NEQ', $pare['id']);
            $account = $model->where($ac_map)->select();
            if (!$account) {
                if (!$model->create($pare, Model::MODEL_UPDATE)) {
                    $this->setReturnVal(Code::ERROR, $model->getError(),StatusCode::DATA_ERROR);
                } else {
                    $map['id'] = array('EQ', $pare['id']);
                    $res = $model->where($map)->save();

                    if (false !== $res) {
                        $this->setReturnVal(Code::OK, Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS, $res);
                    } else {
                        $this->setReturnVal(Code::ERROR, Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                    }
                }
            } else {
                $this->setReturnVal(Code::ERROR, Msg::PHONE_EMAIL_ACCOUNT,StatusCode::PHONE_EMAIL_ACCOUNT);
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 删除管理员
     * @param int $id 管理员ID
     */
    public function delManager($id)
    {
        if (!empty($id)) {
            $model = D('Manager');
            $map['id'] = array("EQ", $id);
            $data['del_flg'] = CommonConst::DEL_FLG_DELETED;
            $res = $model
                ->where($map)
                ->save($data);

            if (false !== $res) {
                $this->setReturnVal(Code::OK, Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS, $res);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }


    /**
     * 重置管理员登录密码
     * @param int $id 管理员ID
     */
    public function resetManagerPassword($id)
    {
        if (!empty($id)) {
            $model = D('Manager');
            $map['id'] = array('EQ', $id);
            $map['del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);

            // 获取管理员手机号码
            $res = $model
                ->field(['phone', 'password'])
                ->where($map)
                ->find();

            if (!empty($res)) {
                // 验证手机号码
                $check = Tools::checkPhone($res['phone']);
                if ($check) {
                    $str = Tools::generatePassword();
                    $new_pass = Tools::generateMd5($str);
                    // 更新新密码
                    $save_res = $this->savePassword($id, $new_pass);

                    if ($save_res['code'] === Code::OK) {
                        // 发送短信
                        $send_res = SmsService::sendTemplateInfo($res['phone'],CommonConst::RESET_ADMIN_PWD,array($str));
                        if ($send_res) { // 成功
                            $this->setReturnVal(Code::OK, Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                        } else { // 失败
                            $this->savePassword($id, $res['password']);
                            $this->setReturnVal(Code::ERROR, Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                        }
                    }
                } else {
                    $this->setReturnVal(Code::ERROR, Msg::PHONE_INVALID,StatusCode::PHONE_INVALID);
                }
            } else {
                $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 修改管理员密码
     * @param  int    $id      管理员ID
     * @param  string $newPass 新密码
     * @return array 结果
     */
    private function savePassword($id, $newPass)
    {
        $model = D('Manager');
        $map['id'] = array("EQ", $id);
        $map['del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);

        $data['password'] = $newPass;

        if (!$model->create($data, Model::MODEL_UPDATE, true)){
            $result = array('code' => Code::ERROR, 'msg' => $model->getError(),StatusCode::DATA_ERROR);
        } else {
            $res = $model->where($map)->save();

            if (false !== $res) {
                $result = array("code" => Code::OK, "msg" => Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
            } else {
                $result = array("code" => Code::ERROR, "msg" => Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
            }
        }
        return $result;
    }

    /**
     * 注册发送手机验证码
     * @param $phone
     */
    public function sendMobileVerificationCode($phone)
    {
        if (!empty($phone)) {
            $send_res = SmsService::sendVerificationCode($phone,CommonConst::USER_REGISTER_SMS);
            if ($send_res) {
                $this->setReturnVal(Code::OK,Msg::SEND_VERIFY_CODE_SUCCESS,StatusCode::SEND_VERIFY_CODE_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::CODE_FAIL_SEND,StatusCode::CODE_FAIL_SEND);
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 验证手机验证码
     * @param $phone
     * @param $code
     * @param $label
     */
    public function ajaxValidateMobileVerificationCode($phone,$code,$label)
    {
        if (!empty($phone) && !empty($code) && !empty($label)) {
            $res = $this->validateVerificationCode($phone,$code,$label);
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::VERIFICATION_CODE_RIGHT,StatusCode::VERIFICATION_CODE_RIGHT);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::VERIFICATION_CODE_ERROR,StatusCode::VERIFICATION_CODE_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 验证验证码
     * author 李文起
     * @param $phone
     * @param $verificationCode
     * @param $label
     * @return bool
     */
    private function validateVerificationCode($phone, $verificationCode, $label){
        $condition = array(
            array($phone, Msg::PHONE_REQUIRED, StatusCode::PHONE_REQUIRED, 'empty'),
            array($phone, Msg::PHONE_INVALID, StatusCode::PHONE_INVALID,'phone'),
            array($verificationCode, Msg::VERIFY_CODE_REQUIRED, StatusCode::VERIFY_CODE_REQUIRED,'empty', 4),
            array($label, Msg::SMS_TEMPLET_ERROR, StatusCode::SMS_TEMPLET_ERROR, 'callback', 'checkLabel')
        );

        if (!$this->validateParams($condition)) {

            //没有通过参数验证，返回相应的错误信息
            $this->setReturnVal(Code::ERROR, $this->getParamError(),$this->getParamErroCode());
            return false;
        } else {

            $res = SmsService::isVerificationCodeCorrect($phone,$verificationCode,$label);
            if (!$res){
                $this->setReturnVal(Code::ERROR, Msg::VERIFICATION_CODE_ERROR,StatusCode::VERIFICATION_CODE_ERROR);
                return false;
            }
            return true;
        }
    }

    /**
     * 管理员消息
     * @param $managerId
     * @param int $pageNo
     * @param int $pageSize
     * @param string $status
     * @param string $startTime
     * @param string $endTime
     */
    public function ManagerMsg($managerId,$pageNo=1, $pageSize=10,  $status = 'null',$startTime = 'null',$endTime='null')
    {
        if (!empty($pageSize) && !empty($pageNo) && !empty($managerId)) {
            if (isset($status) && $status!='null'){
                $where['status'] = array('EQ',$status);
            }
            if (isset($startTime)&&$startTime!=="null"){
                $where["create_time"] = array("EGT", $startTime);
            }
            if (isset($endTime)&&$endTime!=="null"){
                $where["create_time"] = array("ELT",$endTime);
            }
            $where['del_flg'] = CommonConst::DEL_FLG_OK;
            $where['manager_id'] = $managerId;
            $model = D('ManagerMsg');
            $totalRecord = $model->where($where)->count();
            $result = $model->where($where)
                ->order('create_time desc')
                ->page($pageNo,$pageSize)
                ->select();

            if ($result !== false) {
                foreach ($result as $key=>&$value){
                    $value['create_time'] = date('Y-m-d ', $value['create_time']);
                    $value['type_str'] = Status::msgType2Str($value['type']);
                    switch ($value['status']) {
                        case Status::USER_MSG_UNREAD:
                            $value['status_str'] = '新消息';
                            break;
                        case Status::USER_MSG_READ;
                            $value['status_str'] = '已读';
                    }

                }
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,array("dataList"=>$result,'totalRecord'=>$totalRecord));
            }else{
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 读消息
     * @param $msgId
     */
    public function readMsg($msgId)
    {
        if (!empty($msgId)) {
            $model = D('ManagerMsg');
            $map['id'] = $msgId;
            $data['status'] = Status::USER_MSG_READ;
            if (!$model->create($data,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
            } else {
                $model->where($map)->save();
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 删除消息
     * @param $msgId
     */
    public function delMsg($msgId)
    {
        if (!empty($msgId)) {
            $model = D('ManagerMsg');
            $map['id'] = $msgId;
            $data['del_flg'] = CommonConst::DEL_FLG_DELETED;
            if (!$model->create($data,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
            } else {
                $model->where($map)->save();
                $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 得到后台管理员消息
     * @param $managerId   管理员id
     * @param $status
     */
    public function getManagerMsgCount($managerId,$status)
    {
        if (isset($managerId) && $managerId) {
            if (isset($status) && $status!='null'){
                $where['status'] = array('EQ',$status);
            }
            $where['manager_id'] = array('EQ',$managerId);

            $model = D('ManagerMsg');
            $totalRecord = $model->where($where)->count();
            if ($totalRecord !== false) {
                $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$totalRecord);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 获得个人信息
     * author 李文起
     * @param $ManagerId
     * @return mixed
     */
    private function getManagerInfo($ManagerId){

        $model = D('Manager');

        $map['id']        = $ManagerId;
        $map['del_flg']   = CommonConst::DEL_FLG_OK;

        return $model->where($map)->find();
    }

    /**
     * 更新个人信息
     * author 李文起
     * @param $ManagerId
     * @param $data
     */
    private function updateManager($ManagerId,$data){

        $model = D('Manager');

        $map['id']      = $ManagerId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if (!$model->create($data,Model::MODEL_UPDATE)) {
            $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
        } else {
            $res = $model->where($map)->save();

            if ($res !== false) {
                $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
            }

        }
    }

    /**
     * 修改个人信息
     * author 李文起
     * @param $managerId
     */
    public function updateManagerInfo($managerId){

        $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (is_numeric($managerId) && isset($pare['name']) && isset($pare['account']) && isset($pare['phone']) && isset($pare['email'])) {

            //获得个人信息
            $ManagerIdInfo =  $this->getManagerInfo($managerId);

            //检测用户名
            $res = $this->checkAccount($pare['account'],$managerId);
            if ($res){

                //检测手机号
                $res = $this->checkEmail($pare['email'],$managerId);
                if ($res){

                    $res = $this->checkPhone($pare['phone'],$managerId);
                    if ($res){
                        if ($pare['oldPassword'] != '') {

                            if ($ManagerIdInfo['password'] != md5($pare['oldPassword'])) {
                                $this->setReturnVal(Code::ERROR,Msg::VERIFY_OLD_PASSWORD_ERROR,StatusCode::VERIFY_OLD_PASSWORD_ERROR);
                            } else {
                                $pare['password'] =  md5($pare['newPassword']);
                                $this->updateManager($managerId,$pare);
                            }
                        } else {
                            $this->updateManager($managerId,$pare);
                        }
                    } else {
                        $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
                    }

                } else {
                    $this->setReturnVal(Code::ERROR,Msg::EMAIL_EXIST,StatusCode::EMAIL_EXIST);
                }

            } else {
                $this->setReturnVal(Code::ERROR,Msg::ACCOUNT_EXIST,StatusCode::ACCOUNT_EXIST);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }
}