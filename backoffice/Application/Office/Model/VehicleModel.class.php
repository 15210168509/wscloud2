<?php
namespace Office\Model;

class VehicleModel extends ApiModel
{
    public function getList($para){
        $url = '/Vehicle/vehicleLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }

    public function searchDevice($serialNo,$companyId)
    {
        return $this->getResult("/Device/searchDevice/serialNo/$serialNo/companyId/$companyId");
    }

    public function getDeviceBySerialNo($serialNo)
    {
        return $this->getResult("/Device/getDeviceBySerialNo/$serialNo/serialNo",'get');
    }

    /**
     * 添加车辆
     */
    public function ajaxAddVehicle($data)
    {
        return $this->getResult("/Vehicle/ajaxAddVehicle",'post',$data);
    }

    /**
     * 删除车辆
     * @param $id
     * @return string
     */
    public function delVehicle($id)
    {
        return $this->getResult("/Vehicle/delVehicle/id/$id",'get');
    }


    /**
     * 添加分组
     * @param $data
     * @return string
     */
    public function addGroups($data)
    {
        return $this->getResult("/Vehicle/addGroups",'post',$data);
    }

    /**
     * 获得分组列表
     * author 李文起
     * @param $companyId
     * @return string
     */
    public function getAllGroups($companyId)
    {
        return $this->getResult("/Vehicle/getAllGroups/companyId/$companyId",'get');
    }

    /**
     * 车辆列表
     * author 李文起
     * @param $companyId
     * @param $groupId
     * @return string
     */
    public function getVehicleListByGroups($companyId,$groupId){
        return $this->getResult("/Vehicle/getVehicleListByGroups/companyId/".$companyId."/groupId/".$groupId,'get');
    }

    /**
     * 编辑车辆
     * author 李文起
     * @param $data
     * @return string
     */
    public function editVehicle($data){
        return $this->getResult("/Vehicle/editVehicle/",'post',$data);
    }

    /**
     * 获取车辆安装设备的安装图片
     * @param $data
     * @return string
     */
    public function checkVehicleDeviceInstallInfo($data)
    {
        return $this->getResult("/Device/checkVehicleDeviceInstallInfo/",'post',$data);
    }

}