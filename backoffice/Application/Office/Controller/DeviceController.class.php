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

class DeviceController extends ListManagementController
{

    public $authentication = true;

    /**
     * 添加设备
     */
    public function add()
    {
        $this->breadcrumb = array("设备管理"=>'#','添加设备'=>'add');
        $model = D('Device');
        $res = $model->getPackage($this->context->loginuser->company_id);
        if ($res['code'] == CommonConst::API_CODE_SUCCESS) {
            $this->assign('devices',$res['data']['devices']-$res['data']['device_use']);
        } else {
            $this->assign('devices',0);
        }
        $this->addJS(array('office/device/addDevice.js'));
        $this->display();
    }

    public function ajaxAddDevice()
    {
        $data['id'] = Tools::generateId();
        $data['name'] = I('post.name');
        $data['serial_no'] = trim(I('post.serial_no'));
        $data['status'] = I('post.status');
        $data['sim_no'] = I('post.sim_no');
        $data['type'] = I('post.type');
        $data['model'] = I('post.model');
        $data['company_id'] = $this->context->loginuser->company_id;

        $model = D('Device');
        $res = $model->ajaxAddDevice($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 设备列表
     */
    public function lists()
    {
        $this->breadcrumb = array("首页"=>'/', "设备管理"=>"#", "设备列表"=>"#");
        $this->addJS(array('office/device/deviceList.js'));
        // 列表检索条件
        $session_para = $this->getSessionParam('Device');

        if (false !== $session_para) {
            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }

            if ($session_para['serialNo'] != 'null' && !empty($session_para['serialNo'])) {
                $this->assign('where_serialNo', $session_para['serialNo']);
            } else {
                $this->assign('where_serialNo', '');
            }
        }
        $this->assign('companyId',$this->context->loginuser->company_id);
        $this->assign('deviceTopic','deviceTopic/'.$this->context->loginuser->phone);
        $this->display('deviceList');
    }

    /**
     * 搜索设备
     */
    public function search()
    {
        $para = array('name'=>'string','serialNo'=>'string','companyId'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Device','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action = '<input type="hidden" lay-device_type="'.$value->type.'" value="'.$value->type.'" />';
                $value->action.= '<button class="layui-btn  layui-btn-xs layui-btn-normal" lay-event="edit">编辑</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="monitor">监控列表</button>';
                if ($value->device_line_status == 10 && $value->company_id != 9437514453) {
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="setting">配置</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="detail">调整配置</button>';
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="restart">重启设备</button>';
                }
                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del">删除</button>';
                $value->action .= '<button style="display: none" class="layui-btn layui-btn-xs" lay-event="open">开</button>';
                $value->action .= '<button style="display: none" class="layui-btn layui-btn-xs" lay-event="off">关</button>';
            }
        }
        return $list;
    }

    /**
     * 激活设备
     * @param $id
     */
    public function activeDevice($id)
    {
        $model = D('Device');
        $res = $model->activeDevice($id);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::DEVICE_ACTIVE_OK));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::DEVICE_ACTIVE_NO));
        }

    }

    /**
     * 编辑设备
     * @return mixed
     */
    public function editDevice()
    {
        $model = D('Device');

        $data['id']                 = I('post.id');
        $data['sim_no']             = I('post.device_sim_no');
        $data['name']               = I('post.device_name_input');
        $data['serial_no']          = trim(I('post.device_no_input'));
        $res = $model->editDevice($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 删除设备
     * @param $id
     *
     */
    public function delDevice($id)
    {
        $model = D('Device');
        $res = $model->delDevice($id);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_SUCCESS));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_ERROR));
        }
    }

    /**
     * 获取设备配置信息
     */
    public function getDeviceSettingInfo()
    {
        $data['serialNo'] = I('post.serialNo');
        $data['topic'] = I('post.topic');
        $data['deviceType'] = I('post.deviceType');
        //获取设备类型
        $data['type'] = I('post.type');
        $model = D('Device');
        $res = $model->getDeviceSettingInfo($data);
        $this->ajaxReturn($res);
    }

    /**
     * 添加配置
     */
    public function addDeviceSetting()
    {
        $this->breadcrumb = array("设备设置"=>'#','添加设置'=>'#');
        $model = D('Device');
        $res = $model->getCompanyDeviceSetting($this->context->loginuser->company_id);
        $this->assign('setting',$res['data']);
        $this->addJS('office/device/deviceSetting.js');
        $this->display();
    }

    /**
     * 提交配置
     */
    public function ajaxAddDeviceSetting()
    {
        $data = $_POST;
        if (!empty($data)) {

            $arr['company_id'] = $this->context->loginuser->company_id;
            $arr['data'] = $data;
            $model = D('Device');
            $res = $model->ajaxAddDeviceSetting($arr);
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING));
        }
    }

    /**
     * 设备配置
     * @param $serialNo
     */
    public function deviceInfo($serialNo)
    {
        $this->breadcrumb = array("设备管理"=>'#','修改设置'=>'#');
        $this->addJS(array('office/device/saveDeviceSetting.js'));
        $this->assign('serialNo',$serialNo);
        $this->assign('deviceTopic','deviceTopic/'.$this->context->loginuser->phone);
        $this->display('deviceInfo');
    }

    /**
     *修改设备配置
     */
    public function ajaxSaveDeviceSetting()
    {
        $data = $_POST;
        if (!empty($data)) {
            $model = D('Device');
            $res = $model->ajaxSaveDeviceSetting($data);
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING));
        }
    }

    /**
     * 重启设备
     */
    public function restartDevice()
    {
        $serialNo = I('post.serialNo');
        $deviceType = I('post.deviceType');
        if (!empty($serialNo)) {
            $model = D('Device');
            $res = $model->restartDevice($deviceType,$serialNo);
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING));
        }
    }

    /**
     * 推送消息
     */
    public function pushMsg()
    {
        $serialNo = I('post.serialNo');
        $deviceType = I('post.deviceType');
        $type = I('post.type');
        if (!empty($serialNo)) {
            $model = D('Device');
            $res = $model->pushMsg($deviceType,$serialNo,$type);
            $this->ajaxReturn($res);
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING));
        }
    }

}