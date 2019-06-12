<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/16
 * Time: 9:29
 */

namespace Office\Model;


class GroupsModel extends ApiModel
{
    /**
     * 获取分组类型
     * author 李文起
     * @return string
     */
    public function getGroupsType(){
        return $this->getResult('/GroupsService/groupsType','get');
    }

    /**
     * 添加分组
     * author 李文起
     * @param $data
     * @return string
     */
    public function addGroups($data){
        return $this->getResult('/GroupsService/addGroups','post',$data);
    }

    /**
     * 获取分组列表
     * author 李文起
     * @param $para
     * @return string
     */
    public function getList($para){

        $url = '/GroupsService/groupsLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }

    /**
     * 修改分组信息
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateGroups($data){
        return $this->getResult("/GroupsService/updateGroups", 'post',$data);
    }

    /**
     * 删除分组
     * author 李文起
     * @param $data
     * @return string
     */
    public function deleteGroups($data){
        $result = $this->getResult("/GroupsService/deleteGroups", 'post',$data);
        return $result;
    }

    /**
     * 搜索车辆
     * author 李文起
     * @param $vehicleNo
     * @param $companyId
     * @return string
     */
    public function searchVehicle($vehicleNo,$companyId)
    {
        return $this->getResult("/GroupsService/searchVehicle/vehicleNo/".$vehicleNo."/companyId/".$companyId);
    }

    /**
     * 添加车辆
     * author 李文起
     * @param $groupId
     * @param $vehicleId
     * @return string
     */
    public function addVehicle($groupId,$vehicleId){
        return $this->getResult("/GroupsService/addVehicle/groupId/".$groupId."/vehicleId/".$vehicleId);
    }

    /**
     * 车辆成员列表
     * author 李文起
     * @param $id
     * @param $pageNo
     * @param $pageSize
     * @param $vehicleNo
     * @param $vehicleModel
     * @param $vehicleStatus
     * @return string
     */
    public function vehicleItemList($id,$pageNo,$pageSize,$vehicleNo = 'null',$vehicleModel = 'null',$vehicleStatus = 'null'){
        return $this->getResult("/GroupsService/vehicleItemList/groupId/".$id."/pageNo/".$pageNo.'/pageSize/'.$pageSize.'/vehicleNo/'.$vehicleNo.'/vehicleModel/'.$vehicleModel.'/vehicleStatus/'.$vehicleStatus);
    }

    /**
     * 移除车辆
     * author 李文起
     * @param $id
     * @return string
     */
    public function deleteVehicle($id){
        return $this->getResult("/GroupsService/deleteVehicle/id/".$id);
    }

    /**
     * 根据类型获取所有分组
     * author 李文起
     * @param $companyId
     * @param $type
     * @return string
     */
    public function typeGroupsLists($companyId,$type){
        return $this->getResult("/GroupsService/typeGroupsLists/companyId/".$companyId."/type/".$type);
    }

    /**
     * 查询公司下车辆分组
     * @param $vehicleId
     * @param $companyId
     * @return string
     */
    public function moveVehicleGroup($vehicleId,$companyId)
    {
        return $this->getResult("/GroupsService/moveVehicleGroup/vehicleId/".$vehicleId."/company_id/".$companyId);
    }

    /**
     * 移动车辆分组
     * @param $id
     * @param $groupId
     * @return string
     */
    public function ajaxMoveVehicleGroup($id,$groupId)
    {
        return $this->getResult("/GroupsService/ajaxMoveVehicleGroup/id/".$id."/groupId/".$groupId);
    }
}