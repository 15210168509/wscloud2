<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 16:13
 */

namespace Office\Model;


class DriverModel extends ApiModel
{

    /**
     * 发送注册短信验证码
     * author 李文起
     * @param $phone
     * @return string
     */
    public function sendRegisterCode($phone){
        $res = $this->getResult('/DriverService/sendWsVerificationCode/phone/'.$phone,'get');
        return $res;
    }

    /**
     * 添加司机
     * author 李文起
     * @param $data
     * @return string
     */
    public function addDriver($data){
        $result = $this->getResult("/DriverService/addDriver", 'post',$data);
        return $result;
    }

    /**
     * 获取司机列表
     * author 李文起
     * @param $para
     * @return string
     */
    public function getList($para){

        $url = '/DriverService/driverLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }

    /**
     * 修改司机信息
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateDriver($data){
        $result = $this->getResult("/DriverService/updateDriver", 'post',$data);
        return $result;
    }

    /**
     * 删除司机
     * author 李文起
     * @param $data
     * @return string
     */
    public function deleteDriver($data){
        $result = $this->getResult("/DriverService/deleteDriver", 'post',$data);
        return $result;
    }

    /**
     * 获得司机信息
     * author 李文起
     * @param $id
     * @param $companyId
     * @return string
     */
    public function getDriverInfo($id,$companyId){
        $result = $this->getResult("/DriverService/driverInfo/id/".$id.'/companyId/'.$companyId, 'get');
        return $result;
    }

    /**
     * 发送注册短信
     * author 李文起
     * @param $phone
     * @return string
     */
    public function sendMobileCode($phone){
        $result = $this->getResult("/DriverService/sendMobileCode/phone/".$phone.'/type/1', 'get');
        return $result;
    }

    /**
     * 微视用户注册
     * author 李文起
     * @param $data
     * @return string
     */
    public function wsUserRegister($data){
        $result = $this->getResult("/DriverService/wsUserRegister/",'post',$data);
        return $result;
    }

    /**
     * 报警集中时间段
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param $driverId
     * @return string
     */
    public function statByTimeGroup($companyId,$startTime,$endTime,$driverId){
        return $this->getResult("/DrivingMonitor/statByTimeGroup/companyId/".$companyId."/startTime/".$startTime."/endTime/".$endTime."/driverId/$driverId",'get');
    }

    /**
     * 疲劳值
     * @param $driverId
     * @return string
     */
    public function driverTiredValue($driverId)
    {
        $result = $this->getResult("/DrivingMonitor/driverTiredValue",'post',array('driverId'=>$driverId));
        return $result;
    }

    /**
     *
     * @param $companyId
     * @param $startTime
     * @param $driverId
     * @return string
     */
    public function driverTiredNoByType($companyId,$startTime,$driverId)
    {
        $result = $this->getResult("/DrivingMonitor/statTiredNo/companyId/$companyId/startTime/$startTime/driverId/$driverId",'get');
        return $result;
    }

    /**
     * 上传人脸图片
     * author 李文起
     * @param $data
     * @return string
     */
    public function uploadFace($data){
        $result = $this->getResult("/DriverService/uploadFace",'post',$data);
        return $result;
    }

}