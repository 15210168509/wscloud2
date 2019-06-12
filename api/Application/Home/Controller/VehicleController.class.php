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
class VehicleController extends AdvancedRestController
{
    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 添加车辆
     * @param $adminId
     */
    public function ajaxAddVehicle($adminId)
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $pare['create_user'] = $adminId;
            $pare['update_user'] = $adminId;
            $model = D('Vehicle');
            //设备号是否已绑定车辆
            $map['device_no'] = $pare['device_no'];
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $is_bind = $model->where($map)->find();
            if ($is_bind) {
                $this->setReturnVal(Code::ERROR,Msg::DEVICE_IS_BIND,StatusCode::DEVICE_IS_BIND);
            } else {
                if (!$model->create($pare,Model::MODEL_INSERT)) {
                    $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
                } else {
                    $res = $model->add();
                    if ($res) {
                        //设置设备绑定车牌号
                        $redis = RedisLock::getInstance();
                        $redis->set('safe_device_'.$pare['device_no'],json_encode(array('vehicle_no'=>$pare['vehicle_no'],'company_id'=>$pare['company_id'])),0);
                        $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                    } else {
                        $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                    }
                }
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 车辆列表
     * @param $pageNo
     * @param $pageSize
     * @param $companyId
     * @param string $vehicle_no
     * @param string $device_no
     */
    public function vehicleLists($pageNo, $pageSize, $companyId='null',$vehicle_no = 'null',$device_no = 'null')
    {
        $model = D('Vehicle');
        $map['v.del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);

        if ($companyId != 'null'){
            $map['v.company_id']  = $companyId;
        }

        if (isset($vehicle_no) && $vehicle_no != 'null') {
            $map['v.vehicle_no'] = array('LIKE', '%'.addslashes($vehicle_no).'%');
        }

        if (isset($device_no) && $device_no != 'null') {
            $map['v.device_no'] = array('LIKE', '%'.addslashes($device_no).'%');
        }


        $totalRecord = $model
            ->alias('v')
            ->join('left join company c on v.company_id = c.id')
            ->where($map)
            ->count();

        $num = ceil($totalRecord/$pageSize);
        if ($pageNo > $num) {
            $pageNo = $num;
        }

        $result = $model
            ->alias('v')
            ->join('left join company c on v.company_id = c.id')
            ->join('left join device d on v.device_id = d.id')
            ->field('v.*,c.name company_name,d.type type')
            ->where($map)
            ->page($pageNo, $pageSize)
            ->order('v.create_time DESC')
            ->select();

        if (count($result) > 0) {
            $redis = RedisLock::getInstance();
            foreach ($result as &$value) {
                $value['create_time'] = $value['create_time'] ? date('Y-m-d H:i:s', $value['create_time']) : '无';
                $value['position_info'] = $redis->get($value['device_no']);
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
     * 删除车辆
     * @param $id
     */
    public function delVehicle($id)
    {
        $model = D('Vehicle');
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
        $this->restReturn();

    }


    /**
     * 添加分组
     */
    public function addGroups()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $model = D('VehicleGroups');

                $res = $model->addAll($pare);
                if ($res) {
                    $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 获取所有分组
     * @param $companyId
     */
    public function getAllGroups($companyId)
    {
        $model = D('Groups');
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['company_id'] = $companyId;
        $map['type'] = CommonConst::VEHICLE_GROUPS;
        $res = $model->where($map)->select();
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 获得车辆列表
     * author 李文起
     * @param string $groupId
     * @param $companyId
     */
    public function getVehicleListByGroups($companyId,$groupId = 'null'){

        $model = D('Vehicle');

        $map['v.del_flg']    = CommonConst::DEL_FLG_OK;
        $map['v.company_id'] = $companyId;

        if ($groupId != 'null') {
            $map['vg.del_flg']   = CommonConst::DEL_FLG_OK;
            $map['vg.group_id']  = $groupId;
            $group = 'vg.id';
        } else {
            $group = 'v.id';
        }

        $res = $model
            ->alias('v')
            ->field('v.*')
            ->join('left join vehicle_groups as vg on vg.vehicle_id=v.id')
            ->order('v.create_time desc')
            ->group($group)
            ->where($map)
            ->select();

        if ($res !== false) {

            $redis = RedisLock::getInstance();
            foreach ($res as &$value){
                $value['name']           = $value['vehicle_no'];
                $value['position_info'] = $redis->get($value['device_no']);
            }

            $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
        }

        $this->restReturn();
    }


    /**
     * 检测车辆是否已经绑定设备
     * author 李文起
     * @param $deviceNo
     * @param string $id
     * @return mixed
     */
    public function checkVehicleBandDevice($deviceNo, $id = 'null'){

        $model = D('Vehicle');

        $map['del_flg']     = CommonConst::DEL_FLG_OK;
        $map['device_no']   = $deviceNo;

        if ($id != 'null'){
            $map['id'] = array('NEQ',$id);
        }

        return $model->where($map)->find();
    }

    /**
     * 编辑车辆
     * author 李文起
     * @param $adminId
     */
    public function editVehicle($adminId){
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            $res = $this->checkVehicleBandDevice($pare['device_no'],$pare['id']);

            if (!empty($res['id'])) {
                $this->setReturnVal(Code::ERROR,Msg::DEVICE_IS_BIND,StatusCode::DEVICE_IS_BIND);
            } else {

                $map['id']               = $pare['id'];
                $map['del_flg']         = CommonConst::DEL_FLG_OK;

                $data['device_no']      = $pare['device_no'];
                $data['update_user']    = $adminId;
                $data['device_id']      = $pare['device_id'];
                $data['vehicle_no']     = $pare['vehicle_no'];
                $data['model']           = $pare['model'];

                $model = D('Vehicle');

                if (!$model->create($pare,Model::MODEL_UPDATE)) {
                    $this->setReturnVal(Code::ERROR,Msg::DATA_ERROR,StatusCode::DATA_ERROR);
                } else {

                    $res = $model->where($map)->save();

                    if ($res > 0) {
                        //设置设备绑定车牌号
                        $redis = RedisLock::getInstance();
                        $redis->set('safe_device_'.$pare['device_no'],json_encode(array('vehicle_no'=>$pare['vehicle_no'],'company_id'=>$pare['company_id'])),0);
                        $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                    } else {
                        $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                    }
                }
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

}