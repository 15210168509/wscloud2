<?php

namespace Home\Controller;

use Lib\AiConst\BehaviorConst;
use Lib\Code;
use Lib\Logger\Logger;
use Lib\Mqtt\MsgPublish;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Think\Model;
use Lib\CommonConst;
use Lib\Tools;
use Lib\SmsService;

/**
 * 公司
 * Class AdminController
 * @package Home\Controller
 */
class CompanyController extends AdvancedRestController
{
    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 公司注册
     */
   public function registerCompany()
   {
       if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {

           $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
           //验证邮箱，手机号是否已注册
           $checkPhone = $this->checkPhone($pare['phone']);
           $checkEmail = $this->checkEmail($pare['email']);
           if ($checkPhone) {
               if ($checkEmail) {
                   $pare['status'] = Status::ADMIN_OK;
                   $pare['create_user'] = $pare['id'];
                   $pare['update_user'] = $pare['id'];
                   $model = D('Company');
                   if (!$model->create($pare,Model::MODEL_INSERT)) {
                       $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                   }  else {
                       //注册公司
                       if ($model->add()) {
                           //创建超级管理员
                           $adminModel = D('Admin');
                           $adminData['id'] = Tools::generateId();
                           $adminData['company_id'] = $pare['id'];
                           $adminData['name'] = $pare['name'];
                           $adminData['account'] = $pare['phone'];
                           $adminData['phone'] = $pare['phone'];
                           $adminData['email'] = $pare['email'];
                           $adminData['create_user'] = $adminData['id'];
                           $adminData['update_user'] = $adminData['id'];
                           $adminData['type']         = CommonConst::ADMIN_TYPE_ADMIN;
                           $password = Tools::generatePassword();
                           $adminData['password'] = md5($password);
                           $adminData['right'] = $pare['right'];
                           if (!$adminModel->create($adminData,Model::MODEL_INSERT)) {
                               Logger::error("创建超级管理员失败，公司id【".$pare['id'].'】');
                           } else {
                               $adminModel->add();
                           }
                           $this->setReturnVal(Code::OK,Msg::REGISTER_SUCCESS,StatusCode::REGISTER_SUCCESS);

                           //设置默认管理员
                           $this->setDefaultAdmin($adminData);

                           //发送短信
                           SmsService::sendTemplateInfo($pare['phone'],CommonConst::REGISTER_SUCCESS_SMS,array($password));

                           //添加公司报警设置
                           $this->setSystemSetting($pare['id'],$adminData['id']);

                           //发送消息
                           $this->sendMangerMsg($pare,$adminData['id']);

                       } else {
                           //注册公司失败
                           $this->setReturnVal(Code::ERROR,Msg::REGISTER_ERROR,StatusCode::REGISTER_ERROR);
                       }
                   }
               } else {
                   $this->setReturnVal(Code::ERROR,Msg::EMAIL_EXIST,StatusCode::EMAIL_EXIST);
               }

           } else {
               $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
           }

       } else {
           $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
       }
       $this->restReturn();
   }

    /**
     * 设置公司设备配置
     * @param $companyId
     * @param $userId
     */
   private function setCompanyDeviceSetting($companyId,$userId)
   {
       $setting = C('DEVICE_SETTING');
       $arr = [];
       foreach ($setting as $k=>$v) {
           $data = [];
           $data['id'] = Tools::generateId();
           $data['company_id'] = $companyId;
           $data['type'] = $k;
           $data['value'] = $v;
           if ($k == 60 || $k== 80 || $k== 90) {
               $data['is_common'] = 0;
           } else {
               $data['is_common'] = 1;
           }
           $data['create_time'] = time();
           $data['update_time'] = time();
           $data['create_user'] = $userId;
           $data['update_user'] = $userId;
           $arr[] = $data;
       }
       $model = D('DeviceSetting');
       $res = $model->addAll($arr);
       if (!$res) {
           Logger::error("创建默认设备配置失败，公司id【".$companyId.'】');
       }
   }

    /**
     * author 李文起
     * @param $admin
     */
   private function setDefaultAdmin($admin){

       $data['id']  = $admin['id'];
       $data['phone']  = $admin['phone'];


       $redis = RedisLock::getInstance();
       $redis->set('safe_admin_'.$admin['company_id'],json_encode(array($data)),0);
   }

    /**
     * 设置报警类型
     * author 李文起
     * @param $companyId
     * @param $createUser
     */
   public function setSystemSetting($companyId,$createUser){

       $model = D('SystemSetting');

       //设置报警类型
       $data[0]['id']             = Tools::generateId();
       $data[0]['company_id']     = $companyId;
       $data[0]['create_user']    = $createUser;
       $data[0]['update_user']    = $createUser;
       $data[0]['type']            = CommonConst::SYSTEM_SET_WARNING;
       $data[0]['value']           = implode(',',BehaviorConst::getBehaviorCode());
       $data[0]['create_time']     = time();
       $data[0]['update_time']     = time();

       //报警弹框
       $data[1]['id']             = Tools::generateId();
       $data[1]['company_id']     = $companyId;
       $data[1]['create_user']    = $createUser;
       $data[1]['update_user']    = $createUser;
       $data[1]['type']            = CommonConst::SYSTEM_SET_WARNING_DIALOG;
       $data[1]['value']           = '1';
       $data[1]['create_time']    = time();
       $data[1]['update_time']    = time();


       $res = $model->addAll($data);

       if ($res !== false) {

           $redis = RedisLock::getInstance();
           $redis->set('safe_monitor_type_'.$companyId, $data[0]['value'],0);

       } else {
           Logger::error('公司注册设置报警类型错误,');
       }
   }

    /**
     * 公司注册成功后向管理员发送消息
     * author 李文起
     * @param $pare
     * @param $adminId
     */
   public function sendMangerMsg($pare,$adminId) {

       //获取默认管理员
       $model = D('Manager');

       $map['type']     = CommonConst::ADMIN_TYPE_ADMIN;
       $map['del_flg']  = CommonConst::DEL_FLG_OK;

       $managerRes = $model->where($map)->find();

       if (count($managerRes)>0){

           $msgModel    = D('ManagerMsg');

           $data['id']           = Tools::generateId();
           $data['manager_id']  = $managerRes['id'];
           $data['title']        = '新公司注册';
           $data['content']      = $pare['name'].'注册成功，等待审核';
           $data['create_user']  = $adminId;
           $data['update_user']  = $adminId;

           if (!$msgModel->create($data,Model::MODEL_INSERT)) {
               officeLogger()->error($pare['name'].'[ID:'.$pare['id'].']注册成功，发送消息失败,原因：'.$model->getError());
           } else {
               $res = $msgModel->add();
               if ($res === false) {
                   officeLogger()->error($pare['name'].'[ID:'.$pare['id'].']注册成功，发送消息失败。');
               } else {
                   MsgPublish::getInstance()->sendMsg(CommonConst::TOPIC_MANAGER . '/' . $managerRes['phone'], '新公司注册');
               }
           }
       }
   }

    /**
     * 验证手机号是否已注册
     * @param $phone
     * @return bool
     */
   private function checkPhone($phone,$except='null')
   {
       $model = D('Company');
       $map['phone'] = $phone;
       $map['del_flg'] = CommonConst::DEL_FLG_OK;
       if (isset($except) && $except != 'null') {
           $map['id'] = array('NEQ',$except);
       }
       $res = $model->where($map)->find();
       if ($res) {
           return false;
       } else {
           return true;
       }
   }

    /**
     * 检测管理员手机号是否存在
     * author 李文起
     * @param $phone
     * @param string $expect
     * @return bool
     */
   private function checkAdminPhone($phone,$expect = 'null'){
       $model = D('Admin');
       $map['phone'] = $phone;
       $map['del_flg'] = CommonConst::DEL_FLG_OK;
       if (isset($expect) && $expect != 'null') {
           $map['id'] = array('NEQ',$expect);
       }
       $res = $model->where($map)->find();
       if ($res) {
           return false;
       } else {
           return true;
       }
   }

    /**
     * 验证邮箱是否已注册
     * @param $email
     * @return bool
     */
   private function checkEmail($email,$except='null')
   {
       $model = D('Company');
       $map['email'] = array('EQ',$email);
       if (isset($except) && $except != 'null') {
           $map['id'] = array('NEQ',$except);
       }
       $map['del_flg'] = CommonConst::DEL_FLG_OK;
       $res = $model->where($map)->find();
       if ($res) {
           return false;
       } else {
           return true;
       }
   }

    /**
     * 获取公司审核状态
     * @param $companyId
     */
   public function checkStatus($companyId)
   {
       $model = D('Company');
       $map['id'] = $companyId;
       $map['del_flg'] = CommonConst::DEL_FLG_OK;
       $res = $model->where($map)->find();
       if ($res) {
           $res['verify_status_str'] = CommonConst::VerifyStatus($res['verify_status']);
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
       } else {
           $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
       }
       $this->restReturn();
   }

    /**
     * 修改公司信息
     */
   public function saveCompanyInfo()
   {
       if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
           $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
           //验证邮箱，手机号是否已注册
           $checkPhone = $this->checkPhone($pare['phone'],$pare['id']);
           $checkEmail = $this->checkEmail($pare['email'],$pare['id']);

           if ($checkPhone) {
               if ($checkEmail) {
                    $model = D('Company');
                   $pare['verify_status'] = CommonConst::VERIFY_STATUS_ING;
                   if ($model->create($pare,Model::MODEL_UPDATE)) {
                       $map['id'] = $pare['id'];
                       $res = $model->where($map)->save();
                       if ($res != false) {
                           $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                       } else {
                           $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                       }
                   } else {
                       $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
                   }
               } else {
                   $this->setReturnVal(Code::ERROR,Msg::EMAIL_EXIST,StatusCode::EMAIL_EXIST);
               }
           } else {
               $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
           }
       } else {
           $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
       }
       $this->restReturn();
   }

    /**
     * 公司审核
     */
   public function verifyCompany($managerId)
   {
       if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
           $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'],true);
           $model = D('Company');
           $map['id'] = $pare['id'];
           $data['verify_status'] = $pare['verify_status'];
           if (!$model->create($data,Model::MODEL_UPDATE)) {
               $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
           } else {
               $res = $model->where($map)->save();
               if ($res !== false) {
                   //消息通知
                   if ($pare['verify_status'] == CommonConst::VERIFY_STATUS_OK) {
                       $msg = '恭喜您的公司审核已通过';
                   } else {
                       $msg = '公司审核未通过，理由：'.$pare['comment'];
                   }
                   $this->setCompanyDeviceSetting($pare['id'],$managerId);
                   $this->sendMsg2Company($pare['id'],$msg,$managerId);
                   $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
               } else {
                   $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
               }
           }
       } else {
           $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
       }
       $this->restReturn();
   }

   private function sendMsg2Company($companyId,$msg,$userId)
   {
       $model = D('Admin');
       $map['company_id'] = $companyId;
       $map['type'] = CommonConst::ADMIN_TYPE_ADMIN;
       $adminInfo = $model->where($map)->find();
       $msgData['id'] = Tools::generateId();
       $msgData['admin_id'] = $adminInfo['id'];
       $msgData['title'] = '公司审核信息';
       $msgData['type'] = CommonConst::MSG_TYPE_SYSTEM;
       $msgData['content'] = $msg;
       $msgData['create_user'] = $userId;
       $msgData['update_user'] = $userId;
       $adminMsgModel = D('AdminMsg');
       $adminMsgModel->create($msgData,Model::MODEL_INSERT);
       $adminMsgModel->add();
       MsgPublish::getInstance()->sendMsg(CommonConst::TOPIC_ADMIN . '/' . $adminInfo['phone'], json_encode(array('msgType' => CommonConst::MSG_TYPE_SYSTEM, 'data' => $msgData)));
   }

    /**
     * 创建套餐
     */
   public function setCompanyPackage($managerId)
   {
       if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
           $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'],true);
           $model = D('CompanyPackage');
           //设置原有套餐过期
           $map['company_id'] = $pare['company_id'];
           $map['expire_status'] = Status::COMPANY_EXPIRE_STATUS_OK;
           $data['expire_status'] = Status::COMPANY_EXPIRE_STATUS_NO;
           $package = $model->where($map)->find();
           //旧套餐过期
           $model->where($map)->save($data);
           //创建新套餐
           $pare_data['id'] = Tools::generateId();
           $pare_data['company_id'] = $pare['company_id'];
           $pare_data['start_time'] = $pare['start_time'];
           $pare_data['end_time']   = $pare['end_time'];
           $pare_data['money']      = $pare['money'];
           if ($package != false && count($package)>0) {
               $pare_data['devices']    = $pare['devices']+$package['devices'];
               $pare_data['device_use'] = $package['device_use'];
           } else {
               $pare_data['devices']    = $pare['devices'];
           }
           $pare_data['create_user'] = $managerId;
           $pare_data['update_user'] = $managerId;
           if (!$model->create($pare_data,Model::MODEL_INSERT)) {
               $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
           } else {
               $res = $model->add();
               if ($res) {
                   $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
               } else {
                   $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
               }
           }
       } else {
           $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
       }
       $this->restReturn();
   }

    /**
     * 公司列表
     * @param $pageNo
     * @param $pageSize
     * @param string $name
     * @param int $parentCompanyId 父级公司流水号
     */
   public function lists($pageNo, $pageSize,$name = 'null',$parentCompanyId = 0)
   {
       $model = D('Company');
       $map['del_flg']   = CommonConst::DEL_FLG_OK;
       $map['parent_id'] = $parentCompanyId;

       if (isset($name) && $name != 'null') {
           $map['name'] = array('like','%'.addslashes($name).'%');
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
               $value['verify_status_str'] = Status::companyVerifyStatus2Str($value['verify_status']);
           }

           $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
       } else {
           $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
       }
       $this->restReturn();
   }

    /**
     * 删除失败
     * @param $id
     */
   public function delCompany($id)
   {
       if (!empty($id)) {
           $model = D('company');
           $map['id'] = $id;
           $data['del_flg'] = CommonConst::DEL_FLG_DELETED;
           if (!$model->create($data,Model::MODEL_UPDATE)) {
               $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
           } else {
               $res = $model->where($map)->save();
               if ($res !== false) {
                   $this->setReturnVal(Code::OK,Msg::DEL_OK,StatusCode::DEL_OK);
               } else {
                    $this->setReturnVal(Code::ERROR,Msg::DEL_NO,StatusCode::DEL_NO);
               }
           }
       } else {
           $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
       }
       $this->restReturn();
   }

    /**
     * 公司详情
     * @param $id
     */
    public function getCompanyInfo($id)
    {
        if (!empty($id)) {
            $model = D('Company');
            $map['id'] = $id;
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $res = $model
                ->where($map)
                ->find();
            if (count($res)>0) {
                $packageModel = D('CompanyPackage');
                $_map['company_id'] = $id;
                $_map['expire_status'] = Status::COMPANY_EXPIRE_STATUS_OK;
                $package = $packageModel->where($_map)->find();
                if (count($package)) {
                    $package['start_time_str'] = date('Y-m-d',$package['start_time']);
                    $package['end_time_str'] = date('Y-m-d',$package['end_time']);
                }
                $res['package'] = $package;
                $res['create_time_str'] = date('Y-m-d',$res['create_time']);
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
     * 查询公司
     * @param $keyword
     */
    public function searchCompany($keyword)
    {
        $model = D('Company');
        $map['name'] = array('LIKE', '%'.addslashes($keyword).'%');
        $map['del_flg'] = array('EQ',CommonConst::DEL_FLG_OK);
        $res = $model->field('name,id')->where($map)->select();
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 预警次数
     * @param $companyId
     */
    public function statBehavior($companyId){
        if (is_numeric($companyId)) {

          $model = D('DrivingMonitor');

            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    = array(array('EGT',strtotime(date('Y-m-d',time()))),array('LT',time()));

            $today = $model
                ->where($map)
                ->count();

            $all_map['del_flg']       = CommonConst::DEL_FLG_OK;
            $all_map['company_id']    = $companyId;
            $all = $model
                ->where($all_map)
                ->count();

            if (false !== $today && $all !==false) {
                $data['today'] = $today;
                $data['all'] = $all;
                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $data);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 公司套餐
     * @param $id
     */
    public function getPackageInfo($id)
    {
        if (!empty($id)) {
            $model = D('CompanyPackage');
            $map['company_id'] = $id;
            $map['expire_status'] = Status::COMPANY_EXPIRE_STATUS_OK;
            $res = $model->where($map)->find();
            if ($res !=false && count($res)>0) {
                $res['start_time_str'] = date('Y-m-d',$res['start_time']);
                $res['end_time_str'] = date('Y-m-d',$res['end_time']);
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::OK,StatusCode::OK);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 添加公司
     */
    public function ajaxAddCompany()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {

            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            //验证邮箱，手机号是否已注册
            $checkPhone = $this->checkPhone($pare['phone']);
            $checkAdminPhone = $this->checkAdminPhone($pare['phone']);
            $checkEmail = $this->checkEmail($pare['email']);
            if ($checkPhone || $checkAdminPhone) {
                if ($checkEmail) {
                    $pare['status'] = Status::ADMIN_OK;
                    $pare['create_user'] = $pare['id'];
                    $pare['update_user'] = $pare['id'];
                    $model = D('Company');
                    if (!$model->create($pare,Model::MODEL_INSERT)) {
                        $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                    }  else {
                        //注册公司
                        if ($model->add()) {
                            //创建超级管理员
                            $adminModel = D('Admin');
                            $admin_map['type'] = CommonConst::ADMIN_TYPE_ADMIN;
                            $admin = $adminModel->where($admin_map)->order('create_time asc')->find();
                            $adminData['id'] = Tools::generateId();
                            $adminData['company_id'] = $pare['id'];
                            $adminData['name'] = $pare['name'];
                            $adminData['account'] = $pare['phone'];
                            $adminData['phone'] = $pare['phone'];
                            $adminData['email'] = $pare['email'];
                            $adminData['create_user'] = $adminData['id'];
                            $adminData['update_user'] = $adminData['id'];
                            $adminData['type']         = CommonConst::ADMIN_TYPE_ADMIN;
                            $adminData['password'] = md5($pare['password']);
                            $adminData['right'] = $admin['right'];
                            if (!$adminModel->create($adminData,Model::MODEL_INSERT)) {
                                Logger::error("创建超级管理员失败，公司id【".$pare['id'].'】');
                            } else {
                                $adminModel->add();
                            }

                            //设置默认管理员
                            $this->setDefaultAdmin($adminData);

                            //设置公司报警类型
                            $this->setSystemSetting($pare['id'],$adminData['id']);

                            $this->setReturnVal(Code::OK,Msg::REGISTER_SUCCESS,StatusCode::REGISTER_SUCCESS);

                        } else {
                            //注册公司失败
                            $this->setReturnVal(Code::ERROR,Msg::REGISTER_ERROR,StatusCode::REGISTER_ERROR);
                        }
                    }
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::EMAIL_EXIST,StatusCode::EMAIL_EXIST);
                }

            } else {
                $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
            }

        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

}