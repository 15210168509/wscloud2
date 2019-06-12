<?php

namespace Home\Controller;

use Lib\Code;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Think\Model;
use Lib\CommonConst;
use Lib\Tools;
use Lib\SmsService;

/**
 * 超级管理员管理
 * Class AdminController
 * @package Home\Controller
 */
class AdminController extends AdvancedRestController
{
    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 管理员列表数据
     */
    public function adminLists($pageNo, $pageSize,$companyId= 'null',$name = 'null', $phone = 'null', $status = 'null')
    {
        $model = D('Admin');

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
        if (isset($companyId) && $companyId != 'null') {
            $map['company_id'] = array('EQ', $companyId);
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
                $value['status_name'] = Status::AdminStatus2Str($value['status']);
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }


    /**
     * 添加管理员
     * @param $adminId
     */
    public function ajaxAddAdmin($adminId)
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
                $pare['create_user'] = $adminId;
                $pare['update_user'] = $adminId;
                $model = D('Admin');
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
     * 设置默认管理员
     * author 李文起
     * @param $companyId
     */
    private function setDefaultAdminInfo($companyId){
        $model = D('Admin');

        $map['type'] = CommonConst::ADMIN_TYPE_ADMIN;
        $map['company_id'] = $companyId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        $res = $model->field('id,phone')->where($map)->select();

        $redis = RedisLock::getInstance();
        $redis->set('safe_admin_'.$companyId,json_encode($res),0);

    }


    /**
     * 验证手机号
     * @param $phone
     * @param $adminId
     * @return bool
     */
    private function checkPhone($phone,$adminId = 'null')
    {
        $model = D('Admin');
        $map['phone'] = $phone;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if ($adminId != 'null') {
            $map['id'] = array('NEQ',$adminId);
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
     * @param $adminId
     * @return bool
     */
    public function checkEmail($email,$adminId = 'null')
    {
        $model = D('Admin');
        $map['email'] = $email;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if ($adminId != 'null') {
            $map['id'] = array('NEQ',$adminId);
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
     * @param $adminId
     * @return bool
     */
    public function checkAccount($account,$adminId = 'null')
    {
        $model = D('Admin');
        $map['account'] = $account;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        if ($adminId != 'null') {
            $map['id']  = array('NEQ',$adminId);
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
     * @param $admin_id
     */
    public function adminDetail($admin_id)
    {
        if (!empty($admin_id)) {
            $model = D('Admin');
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $map['id'] = $admin_id;
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
    public function ajaxEditAdmin()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {

            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            $model = D('Admin');
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

                        //设置默认管理员
                        $this->setDefaultAdminInfo($pare['company_id']);

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
    public function delAdmin($id)
    {
        if (!empty($id)) {
            $model = D('Admin');
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
    public function resetAdminPassword($id)
    {
        if (!empty($id)) {
            $model = D('Admin');
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
        $model = D('Admin');
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
     * @param $adminId
     * @param int $pageNo
     * @param int $pageSize
     * @param string $status
     * @param string $startTime
     * @param string $endTime
     */
    public function adminMsg($adminId,$pageNo=1, $pageSize=10,  $status = 'null',$startTime = 'null',$endTime='null')
    {
        if (!empty($pageSize) && !empty($pageNo) && !empty($adminId)) {
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
            $where['admin_id'] = $adminId;
            $model = D('AdminMsg');
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
            $model = D('AdminMsg');
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
            $model = D('AdminMsg');
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
     * @param $adminId   管理员id
     * @param $status
     */
    public function getAdminMsgCount($adminId,$status)
    {
        if (isset($adminId) && $adminId) {
            if (isset($status) && $status!='null'){
                $where['status'] = array('EQ',$status);
            }
            $where['admin_id'] = array('EQ',$adminId);

            $model = D('AdminMsg');
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
     * @param $adminId
     * @return mixed
     */
    private function getAdminInfo($adminId){

        $model = D('Admin');

        $map['id']        = $adminId;
        $map['del_flg']   = CommonConst::DEL_FLG_OK;

        return $model->where($map)->find();
    }

    /**
     * 更新个人信息
     * author 李文起
     * @param $adminId
     * @param $data
     */
    private function updateAdmin($adminId,$data){

        $model = D('Admin');

        $map['id']      = $adminId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        if (!$model->create($data,Model::MODEL_UPDATE)) {
            $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
        } else {
            $res = $model->where($map)->save();

            if ($res !== false) {

                //设置默认管理员
                $this->setDefaultAdminInfo($data['company_id']);

                $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
            }

        }
    }

    /**
     * 修改个人信息
     * author 李文起
     * @param $adminId
     */
    public function updateAdminInfo($adminId){

        $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (is_numeric($adminId) && isset($pare['name']) && isset($pare['account']) && isset($pare['phone']) && isset($pare['email'])) {

            //获得个人信息
            $adminIdInfo =  $this->getAdminInfo($adminId);

            //检测用户名
            $res = $this->checkAccount($pare['account'],$adminId);
            if ($res){

                //检测手机号
                $res = $this->checkEmail($pare['email'],$adminId);
                if ($res){
                    $pare['company_id'] = $adminIdInfo['company_id'];
                    $res = $this->checkPhone($pare['phone'],$adminId);
                    if ($res){
                        if ($pare['oldPassword'] != '') {

                            if ($adminIdInfo['password'] != md5($pare['oldPassword'])) {
                                $this->setReturnVal(Code::ERROR,Msg::VERIFY_OLD_PASSWORD_ERROR,StatusCode::VERIFY_OLD_PASSWORD_ERROR);
                            } else {
                                $pare['password'] =  md5($pare['newPassword']);
                                $this->updateAdmin($adminId,$pare);
                            }
                        } else {
                            $this->updateAdmin($adminId,$pare);
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