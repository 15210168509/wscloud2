<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Office\Controller;

use Lib\BaseManagementController;
use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;
use Lib\Status;
use Lib\Tools;

class VehicleController extends ListManagementController
{

    public $authentication = true;

    /**
     * 添加车辆
     */
    public function add()
    {
        $this->breadcrumb = array("车辆管理"=>'#','添加车辆'=>'add');
        $this->addJS(array('office/vehicle/addVehicle.js'));
        $this->display();
    }

    /**
     * 查询设备
     * @param $keywords
     */
    public function searchDevice($keywords)
    {
        $model = D('Vehicle');
        $res = $model->searchDevice($keywords,$this->context->loginuser->company_id);
        $this->ajaxReturn(array('code'=>$res['code'],'content'=>$res['data']));
    }

    /**
     * ajax 添加车辆
     */
    public function ajaxAddVehicle()
    {
        $model = D('Vehicle');
        $data['id'] = Tools::generateId();
        $data['company_id'] = $this->context->loginuser->company_id;
        $data['model'] = I('post.model');
        $data['vehicle_no'] = I('post.vehicle_no');
        $data['device_no']  = I('post.serial_no');

        if (I('post.device_id')) {
            $data['device_id'] = I('post.device_id');
        } else {
            $deviceRes = $model->getDeviceBySerialNo(I('post.serial_no'));
            if ($deviceRes['code'] == Code::OK) {
                $data['device_id'] = $deviceRes['data']['id'];
            } else {
                $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::DEVICE_NO_EXIST));
            }
        }
        $res = $model->ajaxAddVehicle($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));

    }

    /**
     * 车辆列表
     */
    public function lists()
    {
        $this->breadcrumb = array("首页"=>'/', "车辆管理"=>"#", "车辆列表"=>"lists");
        $this->addJS(array('office/vehicle/vehicleList.js'));

        // 列表检索条件
        $session_para = $this->getSessionParam('Vehicle');

        if (false !== $session_para) {
            if ($session_para['vehicle_no'] != 'null' && !empty($session_para['vehicle_no'])) {
                $this->assign('where_vehicle_no', $session_para['vehicle_no']);
            } else {
                $this->assign('where_vehicle_no', '');
            }

            if ($session_para['device_no'] != 'null' && !empty($session_para['device_no'])) {
                $this->assign('where_device_no', $session_para['device_no']);
            } else {
                $this->assign('where_device_no', '');
            }

        }
        $model = D('Vehicle');
        $groups = $model->getAllGroups ($this->context->loginuser->company_id);
        if ($groups['code'] == Code::OK) {
            $this->assign('groups',$groups['data']);
        }

        $this->assign('companyId',$this->context->loginuser->company_id);
        $this->assign('deviceTopic','deviceTopic/'.$this->context->loginuser->phone);
        $this->display('vehicleList');
    }

    /**
     * 搜索员工
     */
    public function search()
    {
        $para = array('vehicle_no'=>'string','companyId'=>"string",'device_no'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Vehicle','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action = '<input type="hidden" lay-device_type="'.$value->type.'" value="'.$value->type.'" />';

                $value->action.= '<button class="layui-btn  layui-btn-xs layui-btn-normal" lay-event="edit">编辑</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="monitor">监控列表</button>';
                if ($value->device_line_status == 10 || $value->company_id ==1051451283) {
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="setting">配置</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="detail">调整配置</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="restart">重启设备</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="update">升级</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="checkDeviceInstallInfo">安装详情</button>';
                }
                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del">删除</button>';
            }
        }
        return $list;
    }

    /**
     * 删除车辆
     * @param $id
     */
    public function delVehicle($id)
    {
        $model = D('Vehicle');
        $res = $model->delVehicle($id);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_SUCCESS));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_ERROR));
        }

    }


    /**
     * 添加分组
     */
    public function addGroups()
    {
        $vehicle = I('post.vehicle');
        $data = [];
        foreach ($vehicle as $value) {
            $vg['vehicle_id'] = $value['id'];
            $vg['group_id'] = I('post.groupId');
            $vg['id'] = Tools::generateId();
            $vg['create_time'] = time();
            $vg['update_time'] = time();
            $data[] = $vg;
        }

        $model = D('Vehicle');
        $res = $model->addGroups($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 编辑车辆
     * author 李文起
     */
    public function editVehicle(){
        $model = D('Vehicle');

        $data['id']                 = I('post.id');
        $data['company_id']         = $this->context->loginuser->company_id;
        $data['model']              = I('post.model');
        $data['vehicle_no']         = I('post.vehicle_no_input');
        $data['device_no']          = trim(I('post.serial_no'));
        $data['device_id']          = I('post.device_id');

        if (I('post.device_id')) {
            $data['device_id'] = I('post.device_id');
        } else {
            $deviceRes = $model->getDeviceBySerialNo(I('post.serial_no'));
            if ($deviceRes['code'] == Code::OK) {
                $data['device_id'] = $deviceRes['data']['id'];
            } else {
                $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::DEVICE_NO_EXIST));
            }
        }
        $res = $model->editVehicle($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    public function checkVehicleDeviceInstallInfo()
    {
        $data['deviceType'] = I('post.deviceType');
        $data['serialNo'] = I('post.serialNo');
        $data['topic'] = I('post.topic');
        $model = D('Vehicle');
        $res = $model->checkVehicleDeviceInstallInfo($data);
        $this->ajaxReturn($res);
    }

    /**
     * 升级
     */
    public function updateDevice()
    {
        $serialNo = I('post.serialNo');
        $deviceType = I('post.deviceType');
        if (!empty($serialNo)) {
            $model = D('Device');
            $res = $model->updateDevice($serialNo,$deviceType);
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING));
        }
    }
}