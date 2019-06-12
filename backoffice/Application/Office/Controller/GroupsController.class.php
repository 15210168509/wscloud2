<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/15
 * Time: 18:27
 */

namespace Office\Controller;


use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;

class GroupsController extends ListManagementController
{
    public function index(){
        die('禁止访问');
    }

    public function add(){

        $model = D('Groups');
        $res = $model->getGroupsType();

        $html = '';
        foreach ($res['data'] as $key=>$value){
            $html .= '<option value='.$key.'>'.$value.'</option>';
        }
        $this->assign('options',$html);

        $this->breadcrumb = array("分组管理"=>"/","添加分组"=>'/');

        $this->addJS("office/groups/add.js");
        $this->display();
    }

    /**
     * 添加分组
     * author 李文起
     */
    public function ajaxAddGroups(){

        $data['name']        = I('post.name');
        $data['type']        = CommonConst::VEHICLE_GROUPS;
        $data['company_id'] = $this->context->loginuser->company_id;

        $model = D('Groups');
        $res = $model->addGroups($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
    }

    /**
     * 分组列表
     * author 李文起
     */
    public function lists(){

        $this->breadcrumb = array("分组管理"=>"/","分组列表"=>'/');

        // 列表检索条件
        $session_para = $this->getSessionParam('Groups');

        $groupType = null;
        if (false !== $session_para) {

            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('name', '');
            }

            if ($session_para['type'] != 'null' && !empty($session_para['type'])) {
               $groupType = $session_para['type'];
            }

        }

        /*$model = D('Groups');
        $res = $model->getGroupsType();

        $html = '';
        foreach ($res['data'] as $key=>$value){
            $html .= '<option value="null">所有分组</option>';
            if ($key == $groupType){
                $html .= '<option value='.$key.' selected>'.$value.'</option>';
            } else {
                $html .= '<option value='.$key.'>'.$value.'</option>';
            }

        }
        $this->assign('options',$html);*/

        $this->addJS("office/groups/lists.js");
        $this->display();
    }

    /**
     * 司机列表
     * author 李文起
     */
    public function ajaxLists(){
        $para = array('name'=>'string'/*,'type'=>'int'*/,'companyId'=>'string');
        $_GET['companyId'] = $this->context->loginuser->company_id;
        $this->setFilterCondition($para);
        $list = $this->getList('Groups','groupsLists');
        $this->ajaxReturn($list,'json');
    }

    /**
     * 分组列表
     * author 李文起
     * @param $list
     * @return mixed
     */
    public function groupsLists($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action = '<div class="layui-table-cell laytable-cell-1-12">';
                $value->action .= '<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">查看组成员</a>';
                $value->action .= '<a class="layui-btn  layui-btn-xs" lay-event="detail">修改</a>';
                $value->action .= '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>';
                $value->action .= '</div>';
            }
        }
        return $list;
    }

    /**
     * 修改分组信息
     * author 李文起
     */
    public function ajaxUpdateGroups(){
        $data['id']                              = I('post.id');
        $data['name']                            = I('post.name');
        $data['company_id']                      = $this->context->loginuser->company_id;

        $model = D('Groups');
        $res = $model->updateGroups($data);

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));

    }

    /**
     * 删除分组
     * author 李文起
     */
    public function ajaxDeleteGroups(){

        $data['id']              = I('post.id');
        $data['company_id']     = $this->context->loginuser->company_id;
        $data['del_flg']        = CommonConst::DEL_FLG_DELETED;

        $model = D('Groups');
        $res = $model->deleteGroups($data);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_SUCCESS,'status_code'=>$res['status_code']));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
        }
    }

    /**
     * 查询设备
     * @param $keywords
     */
    public function searchVehicle($keywords)
    {
        $model = D('Groups');
        $res = $model->searchVehicle($keywords,$this->context->loginuser->company_id);
        $this->ajaxReturn(array('code'=>$res['code'],'content'=>$res['data']));
    }

    /**
     * 分组添加车辆
     * author 李文起
     */
    public function ajaxAddVehicle(){

        $model = D('Groups');
        $res = $model->addVehicle(I('post.groupsId'),I('post.vehicleId'));
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
    }

    /**
     * 车辆成员列表
     * author 李文起
     * @param $id
     */
    public function vehicleItemList($id){
        $this->breadcrumb = array("分组管理"=>"/","查看组成员"=>'/');

        $this->addJS('office/groups/vehicleItemList.js');
        $this->assign('groupsId',$id);

        $session_para = $this->getState('vehicle_list_where');

        if (false !== $session_para) {

            if ($session_para['vehicle_no'] != 'null' && !empty($session_para['vehicle_no'])) {
                $this->assign('where_vehicle_no', $session_para['vehicle_no']);
            } else {
                $this->assign('where_vehicle_no', '');
            }

            if ($session_para['model'] != 'null' && !empty($session_para['model'])) {
                $this->assign('where_model', $session_para['model']);
            } else {
                $this->assign('where_model', '');
            }

            if ($session_para['status'] != 'null' && !empty($session_para['status'])) {
                $this->assign('where_status', $session_para['status']);
            } else {
                $this->assign('where_status', '');
            }

        }


        $this->display();
    }

    /**
     * 车辆成员列表
     * author 李文起
     * @param $id
     * @param $page
     * @param $limit
     * @param $vehicleNo
     * @param $vehicleModel
     * @param $vehicleStatus
     * @param $is_bt
     */
    public function ajaxVehicleItemList($id,$page,$limit,$vehicleNo = 'null',$vehicleModel = 'null',$vehicleStatus = 'null',$is_bt = 0){

        if ($is_bt == 1) {
            $param = array();
            $param['vehicle_no']    = isset($vehicleNo) ? $vehicleNo : 'null';
            $param['model']         = isset($vehicleModel)? $vehicleModel : 'null';
            $param['status']        = isset($vehicleStatus) ? $vehicleStatus :'null';
            $this->setState('vehicle_list_where',$param);
        }
        $param = $this->getState('vehicle_list_where');

        $model = D('Groups');
        $res = $model->vehicleItemList($id,$page,$limit,$param['vehicle_no'],$param['model'],$param['status']);

        $res = json_decode(json_encode($res));
        if ($res->code == Code::OK) {

            foreach ($res->data->dataList as $key=>&$value) {
                $value->action .= '<div class="layui-table-cell laytable-cell-1-12">';
                $value->action .= '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>';
                $value->action .= '<a class="layui-btn layui-btn-xs" lay-event="move">移动</a>';
                $value->action .= '</div>';
            }

            $_res = array(
                "draw" => $_GET["draw"],
                "recordsTotal" => $res->data->totalRecord,
                "recordsFiltered" => $res->data->totalRecord,
                "data" => $res->data->dataList,
                'code'=>0,
                'count'=>$res->data->totalRecord,
            );
            if (C("Debug")) {
                $_res["Debug"] = array("java" => $res, "paras" => array($id,$page,$limit,$vehicleNo ,$vehicleModel ,$vehicleStatus));
            }

        } else {
            $_res = array(
                "draw" => $_GET["draw"],
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                'code'=>0,
                "data" => array()
            );

        }

        $this->ajaxReturn($_res,'json');
    }

    /**
     * 移除车辆
     * author 李文起
     * @param $id
     */
    public function ajaxDeleteVehicle($id){
        $data['id']              = I('post.id');

        $model = D('Groups');
        $res = $model->deleteVehicle($id);

        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_SUCCESS,'status_code'=>$res['status_code']));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
        }

    }

    public function moveVehicleGroup($id)
    {
        $model = D('Groups');
        $res = $model->moveVehicleGroup($id,$this->context->loginuser->company_id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 移动车辆分组
     */
    public function ajaxMoveVehicleGroup()
    {
        $id = I('post.id');
        $group = I('post.groupId');
        $model = D('Groups');
        $res = $model->ajaxMoveVehicleGroup($id,$group);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }
}