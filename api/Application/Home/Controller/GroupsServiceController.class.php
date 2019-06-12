<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/15
 * Time: 18:40
 */

namespace Home\Controller;


use Lib\Code;
use Lib\CommonConst;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Lib\Tools;
use Think\Model;

class GroupsServiceController extends AdvancedRestController
{
    /**
     * 得到分组类型
     * author 李文起
     */
    public function groupsType(){
        $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,CommonConst::getGroupsType());
        $this->restReturn();
    }

    /**
     * 添加分组
     * author 李文起
     * @param $adminId
     */
    public function addGroups($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (is_numeric($adminId) && isset($param['name']) && is_numeric($param['type']) && is_numeric($param['company_id'])) {

            $model = D('Groups');

            $param['id']             = Tools::generateId();
            $param['create_user']   = $adminId;
            $param['update_user']   = $adminId;

            if (!$model->create($param,Model::MODEL_INSERT)) {
                $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
            } else {
                $res = $model->add();
                if ($res) {
                    $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                } else {
                    $this->setReturnVal(Code::OK,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                }
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * author 李文起
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @param string $name
     * @param $type
     */
    public function groupsLists($companyId,$pageNo, $pageSize, $name = 'null',$type = 'null')
    {
        $model = D('Groups');

        $map['del_flg']     = array('EQ', CommonConst::DEL_FLG_OK);
        $map['company_id']  = $companyId;

        if (isset($name) && $name != 'null') {
            $map['name'] = array('LIKE', '%'.addslashes($name).'%');
        }

        if (isset($type) && $type != 'null') {
            $map['type'] = $type;
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
                $value['type_name']    =  CommonConst::getGroupsType()[$value['type']];
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 更新分组信息
     * author 李文起
     * @param $data
     * @param $id
     * @param $updateId
     */
    private function updateGroupsInfo($data,$id,$updateId){
        $model = D('Groups');

        $map['id']              = $id;
        $map['del_flg']         = CommonConst::DEL_FLG_OK;
        $map['company_id']     = $data['company_id'];

        $data['update_user']   = $updateId;

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
     * 更新司机信息
     * author 李文起
     * @param $adminId
     */
    public function updateGroups($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (is_numeric($adminId) && isset($param['id']) && isset($param['name'])) {

            //修改信息
            $this->updateGroupsInfo($param,$param['id'],$adminId);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 删除司机
     * author 李文起
     * @param $adminId
     */
    public function deleteGroups($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if (isset($param['id']) && isset($param['company_id'])) {
            //修改信息
            $this->updateGroupsInfo($param,$param['id'],$adminId);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 搜索车辆
     * author 李文起
     * @param $adminId
     * @param $companyId
     * @param $vehicleNo
     */
    public function searchVehicle($adminId,$companyId,$vehicleNo){

        if (is_numeric($adminId) && is_numeric($companyId) && isset($vehicleNo)) {

            $model = D('Vehicle');

            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $map['company_id']  = $companyId;
            $map['vehicle_no']  = array('LIKE', '%'.addslashes($vehicleNo).'%');

            $res = $model
                ->field('id,vehicle_no')
                ->where($map)
                ->select();
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * author 李文起
     * @param $groupId
     * @param $vehicleId
     */
    public function addVehicle($groupId,$vehicleId){
        if (is_numeric($groupId) && is_numeric($vehicleId)) {

            $model = D('VehicleGroups');

            $map['group_id']    = $groupId;
            $map['vehicle_id']  = $vehicleId;
            $map['del_flg']     = CommonConst::DEL_FLG_OK;

            $data['id']         = Tools::generateId();
            $data['group_id']   = $groupId;
            $data['vehicle_id'] =$vehicleId;

            $res = $model->where($map)->find();
            if (!empty($res)){

                $this->setReturnVal(Code::ERROR,Msg::VEHICLE_EXIST,StatusCode::VEHICLE_EXIST);
            } else {

                if (!$model->create($data,Model::MODEL_INSERT)) {
                    $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                } else {
                    $res = $model->add();
                    if ($res !== false){
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
     * 车辆成员列表
     * author 李文起
     * @param $groupId
     * @param $pageNo
     * @param $pageSize
     * @param $vehicleNo
     * @param $vehicleModel
     * @param $vehicleStatus
     * @return string
     */
    public function vehicleItemList($groupId,$pageNo,$pageSize,$vehicleNo = 'null',$vehicleModel = 'null',$vehicleStatus = 'null'){
        if (is_numeric($groupId) && is_numeric($pageNo) && is_numeric($pageSize)) {

            $model = D('VehicleGroups');

            $map['vg.del_flg']  = CommonConst::DEL_FLG_OK;
            $map['v.del_flg']   = CommonConst::DEL_FLG_OK;
            $map['vg.group_id'] = $groupId;

            if ($vehicleNo != 'null') {
                $map['v.vehicle_no']  = array('LIKE', '%'.addslashes($vehicleNo).'%');;
            }
            if ($vehicleModel != 'null') {
                $map['v.model'] = array('LIKE', '%'.addslashes($vehicleModel).'%');
            }
            if ($vehicleStatus != 'null') {
                $map['v.status']  = $vehicleStatus;
            }

            $totalRecord = $model
                ->alias('vg')
                ->join('left join vehicle as v on v.id=vg.vehicle_id')
                ->where($map)
                ->count();

            $num = ceil($totalRecord / $pageSize);
            if ($pageNo > $num) {
                $pageNo = $num;
            }

            $result = $model
                ->alias('vg')
                ->field('vg.create_time,vg.id,v.vehicle_no,v.model,v.status')
                ->join('left join vehicle as v on v.id=vg.vehicle_id')
                ->where($map)
                ->page($pageNo, $pageSize)
                ->order('vg.create_time DESC')
                ->select();

            if (count($result) > 0) {

                foreach ($result as &$value) {
                    $value['status_name'] = Status::AdminStatus2Str($value['status']);
                    $value['create_time'] = date('Y-m-d',$value['create_time']);
                }

                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, array('dataList' => $result, 'totalRecord' => $totalRecord));
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 移除车辆
     * author 李文起
     * @param $id
     */
    public function deleteVehicle($id){
        if (is_numeric($id)) {

            $model = D('VehicleGroups');

            $map['id']       = $id;
            $map['del_flg'] = CommonConst::DEL_FLG_OK;

            $data['del_flg'] = CommonConst::DEL_FLG_DELETED;

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

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 根据类型获得所有分组
     * author 李文起
     * @param $companyId
     * @param string $type
     */
    public function typeGroupsLists($companyId,$type = 'null')
    {
        $model = D('Groups');

        $map['del_flg'] = array('EQ', CommonConst::DEL_FLG_OK);
        $map['company_id'] = $companyId;
        if ($type != 'null') {
            $map['type']    = $type;
        }

        $res = $model->field('id,name,type')->where($map)->select();

        if ($res !== false) {
            $res = array_merge(array(array('name'=>'全部车辆','id'=>'null')),$res) ;

            foreach ($res as $key=>&$value){
                $vehicleList = $this->getVehicleListByGroups($companyId,$value['id']);
                if (count($vehicleList) > 0){
                    $value['children'] = $vehicleList;
                } else {
                    unset($res[$key]);
                }
            }

            $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
        } else {
            $this->setReturnVal(Code::OK,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
        }

        $this->restReturn();
    }

    /**
     * 获得车辆列表
     * author 李文起
     * @param string $groupId
     * @param $companyId
     */
    private function getVehicleListByGroups($companyId,$groupId = 'null'){

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
                $value['group_id']       = $groupId;
                $value['name']           = '<input id="input_'.$groupId.'_'.$value['id'].'" data="'.$value['device_no'].'_'.$value['vehicle_no'].'"  class="input_device" type="checkbox" style="vertical-align: middle; margin-top: 0px;margin-bottom: 1px;"/>'.$value['vehicle_no'];
                $value['position_info'] = $redis->get($value['device_no']);
            }
        }
        return $res;
    }

    public function moveVehicleGroup($vehicleId,$company_id)
    {
        $model = D('VehicleGroups');
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['id'] = $vehicleId;
        $vehicleGroup = $model->where($map)->find();
        if ($vehicleGroup !== false && count($vehicleGroup)>0) {
            $Gmodel = D('Groups');
            $g_map['del_flg'] = CommonConst::DEL_FLG_OK;
            $g_map['company_id'] = $company_id;
            $g_map['id'] = array('NEQ',$vehicleGroup['group_id']);
            $res = $Gmodel->where($g_map)->select();
            if ($res !== false && count($res)>0) {
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
        }
        $this->restReturn();
    }

    public function ajaxMoveVehicleGroup($id,$groupId)
    {
        $model = D('VehicleGroups');
        $map['id'] = $id;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $vehicleGroup = $model->where($map)->find();
        if ($vehicleGroup !== false) {
            $data['del_flg'] = CommonConst::DEL_FLG_DELETED;
            if (!$model->create($data,Model::MODEL_UPDATE)) {
                $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
            } else {
                $res = $model->where($map)->save();
                if ($res !== false) {
                    //添加新分组
                    $add_data['id'] = Tools::generateId();
                    $add_data['group_id'] = $groupId;
                    $add_data['vehicle_id'] = $vehicleGroup['vehicle_id'];
                    if (!$model->create($add_data,Model::MODEL_INSERT)) {
                        $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                    } else {
                        $flg = $model->add();
                        if ($flg) {
                            $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
                        } else {
                            $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                        }
                    }
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                }
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
        }
        $this->restReturn();
    }

}