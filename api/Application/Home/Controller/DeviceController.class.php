<?php

namespace Home\Controller;

use Lib\Code;
use Lib\JPushSetting;
use Lib\JPushTools;
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
 * 超级管理员管理
 * Class AdminController
 * @package Home\Controller
 */
class DeviceController extends AdvancedRestController
{
    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 判断设备是否已经添加过
     * author 李文起
     * @param $deviceNo
     * @return bool
     */
    private function checkDeviceExist($deviceNo){

        $map['del_flg']     = CommonConst::DEL_FLG_OK;
        $map['serial_no']   = $deviceNo;

        $model = D('Device');

        $res = $model->where($map)->find();

        return $res;

    }

    /**
     * 添加设备
     */
    public function ajaxAddDevice($adminId)
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            //判断设备是否已经添加过
            $res = $this->checkDeviceExist($pare['serial_no']);
            if (empty($res['id'])) {
                $packageModel = D('CompanyPackage');
                $map['company_id'] = $pare['company_id'];
                $map['del_flg'] = CommonConst::DEL_FLG_OK;
                $map['expire_status'] = Status::COMPANY_EXPIRE_STATUS_OK;
                $package = $packageModel->where($map)->find();
                if ($package['device_use']>=$package['devices']) {
                    $this->setReturnVal(Code::ERROR,Msg::PACKAGE_NO_ENOUGH,StatusCode::OK);
                } else {
                    $pare['create_user'] = $adminId;
                    $pare['update_user'] = $adminId;
                    $model = D('Device');
                    if (!$model->create($pare,Model::MODEL_INSERT)) {
                        $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
                    } else {
                        $res = $model->add();
                        if ($res) {

                            //套餐设备使用数
                            $this->addDeviceUse($pare['company_id'],$package['device_use']+1);

                            //设备所属公司
                            $redis = RedisLock::getInstance();
                            $redis->set('safe_device_'.$pare['serial_no'],json_encode(array('company_id'=>$pare['company_id'])),0);

                            $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                        } else {
                            $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                        }
                    }
                }
            } else {
                $this->setReturnVal(Code::ERROR,Msg::DEVICE_EXIST,StatusCode::DEVICE_EXIST);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    private function addDeviceUse($companyId,$num)
    {
        $packageModel = D('CompanyPackage');
        $map['company_id'] = $companyId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['expire_status'] = Status::COMPANY_EXPIRE_STATUS_OK;
        $data['device_use'] = $num;
        $packageModel->create($data,Model::MODEL_UPDATE);
        $packageModel->where($map)->save();
    }

    /**
     * 设备列表
     * @param $pageNo
     * @param $pageSize
     * @param $companyId
     * @param string $name
     * @param string $serialNo
     */
    public function deviceLists($pageNo, $pageSize, $companyId='null',$name = 'null', $serialNo = 'null')
    {

        $model = D('Device');
        $map['d.del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);
        if ($companyId != 'null') {
            $map['d.company_id'] = $companyId;
        }
        if (isset($name) && $name != 'null') {
            $map['d.name'] = array('LIKE', '%'.addslashes($name).'%');
        }
        if (isset($serialNo) && $serialNo != 'null') {
            $map['d.serial_no'] = array('LIKE', '%'.addslashes($serialNo).'%');
        }

        $totalRecord = $model
            ->alias('d')
            ->join('left join company c on d.company_id = c.id')
            ->where($map)
            ->count();

        $num = ceil($totalRecord/$pageSize);
        if ($pageNo > $num) {
            $pageNo = $num;
        }

        $result = $model
            ->field('d.*,c.name company_name')
            ->alias('d')
            ->join('left join company c on d.company_id = c.id')
            ->where($map)
            ->page($pageNo, $pageSize)
            ->order('d.create_time DESC')
            ->select();

        if (count($result) > 0) {
            $redis = RedisLock::getInstance();
            foreach ($result as &$value) {
                $value['create_time'] = $value['create_time'] ? date('Y-m-d H:i:s', $value['create_time']) : '无';
                $value['status_str'] = Status::AdminStatus2Str($value['status']);
                $value['type_str'] = Status::deviceType2Str($value['type']);
                $value['active_str'] = Status::active2Str($value['active']);
                $value['position_info'] = $redis->get($value['serial_no']);
                if ($value['position_info']) {
                    $value['time'] = ($value['position_info']['gps_time']);
                    if ((time() - $value['position_info']['gps_time'])>=1800) {
                        //离线
                        $value['device_line_status'] = Status::DEVICE_OFF_LINE;
                        $value['device_line_status_str'] = '离线';
                    } else {
                        //在线
                        $value['device_line_status'] = Status::DEVICE_ON_LINE;
                        $value['device_line_status_str'] = '在线';
                    }
                } else {
                    //离线
                    $value['device_line_status'] = Status::DEVICE_OFF_LINE;
                    $value['device_line_status_str'] = '离线';
                }
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 激活
     * @param $id
     */
    public function activeDevice($id)
    {
        if (!empty($id)) {
            $model = D('Device');
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $map['id'] = $id;
            $data['active'] = Status::ACTIVE_OK;
            if (!$model->create($data,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
            } else {
                $res = $model->where($map)->save();
                if ($res) {
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

    /**
     * 编辑设备
     */
    public function editDevice($adminId)
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            $map['id']              = $pare['id'];
            $data['serial_no']      = $pare['serial_no'];
            $data['update_user']    = $adminId;
            $data['sim_no']         = $pare['sim_no'];
            $data['name']           = $pare['name'];

            $model = D('Device');

            if (!$model->create($pare,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
            } else {

                $res = $model->where($map)->save();
                if ($res > 0) {
                    $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                }
            }
        }

        $this->restReturn();
    }
    /**
     * 删除设备
     * @param $id
     */
    public function delDevice($id)
    {
        if (!empty($id)) {
            $model = D('Device');
            $map['id'] = $id;
            $data['del_flg'] = CommonConst::DEL_FLG_DELETED;
            if (!$model->create($data,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
            } else {
                $res = $model->where($map)->save();
                if ($res) {
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

    /**
     * 根据设备号查找设备
     * @param $serialNo
     * @param $companyId
     */
    public function searchDevice($serialNo,$companyId)
    {
        $model = D('Device');
        $map['serial_no'] = array('LIKE', '%'.addslashes($serialNo).'%');
        $map['del_flg'] = array('EQ',CommonConst::DEL_FLG_OK);
        $map['company_id']  = $companyId;
        $res = $model->field('serial_no,id')->where($map)->select();
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    public function getDeviceBySerialNo($serialNo)
    {
        $model = D('Device');
        $map['serial_no'] = array('EQ', $serialNo);
        $map['del_flg'] = array('EQ',CommonConst::DEL_FLG_OK);
        $res = $model->field('id')->where($map)->find();
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 读取设备配置信息
     */
    public function getDeviceSettingInfo()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $data['type'] = Status::GET_DEVICE_SETTING;
            //根据设备类型获取不同的推送配置
            $setting = JPushSetting::getSettings($data['deviceType']);
            //$res = JPushTools::getInstance($setting[0],$setting[1])->sendMessageByAlias($data['serialNo'],'消息','获取设备信息',$data);
            $res = true;
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 将设备配置信息推送给前端
     */
    public function sendDeviceInfo()
    {
        $param = $_POST;
        if (!empty($param)) {
            $arr = [];
            foreach ($param as $key=>$value) {
                $name = getSettingName($key);
                if ($name != '') {
                    $arr[$key] = array(
                        'name'=>getSettingName($key),
                        'valueStr'=>getSettingValue($key,$value),
                        'value'=>$value,
                    );
                }
            }
            $data = array(
                'code'=>  Code::OK,
                'data'=>  $arr,
                'msg' =>   Msg::OK
            );
            MsgPublish::getInstance()->sendMsg($_POST['topic'], json_encode($data));
        } else {
            $data = array(
                'code'=>  Code::ERROR,
                'msg' =>   Msg::NO_DATA
            );
            MsgPublish::getInstance()->sendMsg($_POST['topic'], json_encode($data));
        }

    }

    /**
     * 设置公司设备配置
     * @param $adminId
     */
    public function ajaxAddDeviceSetting($adminId)
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $model = D('DeviceSetting');
            $set_flg = true;
            foreach ($data['data'] as $k=>$v) {
                $map['company_id'] = $data['company_id'];
                $map['type'] = $k;
                $update_data['value'] = $v;
                $update_data['update_user'] = $adminId;
                $setInfo = $model->where($map)->find();
                if ($setInfo != false && count($setInfo)>0) {
                    //修改
                    if ($model->create($update_data,Model::MODEL_UPDATE)) {
                        $res = $model->where($map)->save();
                        if ($res) {
                            $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
                        } else {
                            $set_flg = false;
                            $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
                            break;
                        }
                    } else {
                        $set_flg = false;
                        $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::SET_ERROR);
                        break;
                    }
                } else {
                    //添加
                    $setData['id'] = Tools::generateId();
                    $setData['company_id'] = $data['company_id'];
                    $setData['type'] = $k;
                    $setData['value'] = $v;
                    $setData['create_user'] = $adminId;
                    $setData['update_user'] = $adminId;
                    if (!$model->create($setData,Model::MODEL_INSERT)) {
                        $set_flg = false;
                        $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
                        break;
                    } else {
                        $model->add();
                        $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
                    }
                }

            }
            if ($set_flg) {
                //向公司下设备推送配置信息
                //$this->pushSettingToAllDevices($data['data'],$data['company_id']);
                //modified by wrf
                $this->pushSettingToAllDevicesByType($data['data'],$data['company_id']);

            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 推送公共配置信息到公司下所有设备
     * @param $setting
     * @param $companyId
     */
    private function pushSettingToAllDevices($setting,$companyId)
    {
        $model = D('Device');
        $map['company_id'] = $companyId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $device = $model->field('serial_no')->where($map)->select();
        if ($device !== false && count($device)>0) {
            $setting['type'] = Status::PUSH_DEVICE_SETTING;
            //$arr = [];
            foreach ($device as $value) {
                //$arr[] = $value['serial_no'];
                JPushTool::getInstance()->sendMessageByAlias($value['serial_no'],'消息','设备配置信息',$setting);
            }
            //JPushTool::getInstance()->sendMessageByAlias($arr,'消息','设备配置信息',$setting);
        }
    }
    /**
     * 推送公共配置信息到公司下所有设备
     * @param $setting array 设备配置
     * @param $companyId string 设备所属公司
     */
    private function pushSettingToAllDevicesByType($setting,$companyId)
    {
        $model = D('Device');
        $map['company_id'] = $companyId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $device = $model->field('serial_no')->where($map)->select();
        if ($device !== false && count($device)>0) {
            $setting['type'] = Status::PUSH_DEVICE_SETTING;
            foreach ($device as $value) {
                //根据设备类型获取不同的推送配置
                $settings = JPushSetting::getSettings($value['type']);
                JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($value['serial_no'],'消息','设备配置信息',$setting);
            }
            //JPushTool::getInstance()->sendMessageByAlias($arr,'消息','设备配置信息',$setting);
        }
    }

    /**
     * 获取公司下设备的配置
     * @param $company_id
     */
    public function getCompanyDeviceSetting($company_id)
    {
        $model = D('DeviceSetting');
        $map['company_id'] = $company_id;
        $res = $model->where($map)->select();
        if ($res !== false && count($res)>0) {
            $data = [];
            foreach ($res as $k=>$v) {
                $data[$v['type']] = $v['value'];
            }
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$data);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 设备拉取配置信息
     */
    public function getDeviceSetting()
    {
        $param = $_POST;
        if (!empty($param)) {
            $model = D('Device');
            $map['serial_no'] = $param['serialNo'];
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $deviceInfo = $model->where($map)->find();
            if ($deviceInfo) {
                $deviceSettingModel = D('DeviceSetting');
                $_map['company_id'] = $deviceInfo['company_id'];
                $_map['is_common'] = 1;
                $res = $deviceSettingModel->where($_map)->select();
                if ($res !== false && count($res)>0) {
                    $arr = [];
                    foreach ($res as $v) {
                        $arr[$v['type']] = $v['value'];
                    }
                    $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$arr);
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
                }
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 修改单个设备配置
     */
    public function ajaxSaveDeviceSetting()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            //推送到设备
            //根据设备类型获取不同的推送配置
            $settings = JPushSetting::getSettings($data['deviceType']);

            $res = JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($data['serialNo'],'消息','设备配置信息',$data['data']);
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 设备重启
     * @param $serialNo string 设备序列号
     * @param $deviceType string 设备类型
     */
    public function restartDevice($deviceType,$serialNo)
    {
        $data['type'] =  Status::DEVICE_RESTART;
        $settings = JPushSetting::getSettings($deviceType);
        $res = JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($serialNo,'消息','设备重启',$data);
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
        }
        $this->restReturn();
    }

    /**
     * 设备启动升级
     * @param $serialNo string 设备序列号
     * @param $deviceType string 设备类型
     */
    public function updateDevice($deviceType,$serialNo)
    {
        $data['type'] =  Status::DEVICE_UPDATE;
        $settings = JPushSetting::getSettings($deviceType);
        $res = JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($serialNo,'消息','设备升级',$data);
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
        }
        $this->restReturn();
    }

    /**
     *获取车辆安装设备的安装图片
     */
    public function checkVehicleDeviceInstallInfo()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $data['type'] =  Status::CHECK_VEHICLE_PICTURE;
            $data['topic'] = $pare['topic'];
            $data['serialNo'] = $pare['serialNo'];
            $settings = JPushSetting::getSettings($pare['deviceType']);
            $res = JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($pare['serialNo'],'消息','检查车辆设备安装情况',$data);
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 将车辆的设备的安装图片发送到前端
     */
    public function sendVehicleDeviceInstallPicture()
    {
        $param = $_POST;
        if (!empty($param)) {
            $param['msgType'] = 10;
            $param['url'] = C('IMG_FILE_SERVER').'/'.$param['url'];
            $data = array(
                'code'=>  Code::OK,
                'data'=>  $param,
                'msg' =>   Msg::OK
            );
            MsgPublish::getInstance()->sendMsg($_POST['topic'], json_encode($data));
        } else {
            $data = array(
                'code'=>  Code::ERROR,
                'msg' =>   Msg::NO_DATA
            );
            MsgPublish::getInstance()->sendMsg($_POST['topic'], json_encode($data));
        }

    }

    /**
     * 推送消息
     * @param $deviceType string 设备类型
     * @param $serialNo string 设备序列号
     * @param $type string 命令代号
     */
    public function pushMsg($deviceType,$serialNo,$type)
    {
        $data['type'] =  $type;
        //根据不同的设备类型获取不同的推送配置
        $settings = JPushSetting::getSettings($deviceType);
        $res = JPushTools::getInstance($settings[0],$settings[1])->sendMessageByAlias($serialNo,'消息','消息',$data);
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
        }
        $this->restReturn();
    }

    public function cmd()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $pare['type'] =  Status::CMD;
            $res = JPushTool::getInstance()->sendMessageByAlias($pare['serialNo'],'消息','调试',$pare);
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::SET_SUCCESS,StatusCode::SET_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::SET_ERROR,StatusCode::SET_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }


}