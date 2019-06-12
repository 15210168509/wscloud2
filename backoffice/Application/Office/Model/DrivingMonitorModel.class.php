<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/22
 * Time: 10:01
 */

namespace Office\Model;


class DrivingMonitorModel extends ApiModel
{
    /**
     * 获取监控列表
     * author 李文起
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @param $startTime
     * @param $endTime
     * @param $name
     * @param $phone
     * @param $code
     * @param $deviceNo
     * @param $vehicleNo
     * @return string
     */
    public function behaviorLists($companyId,$pageNo,$pageSize,$startTime = 'null',$endTime = 'null',$name='null',$phone = 'null',$code,$deviceNo,$vehicleNo){
        return $this->getResult("/DrivingMonitor/getBehaviorLists/companyId/".$companyId."/pageNo/".$pageNo."/pageSize/".$pageSize."/startTime/".$startTime."/endTime/".$endTime."/name/".$name."/phone/".$phone.'/code/'.$code.'/deviceNo/'.$deviceNo.'/vehicleNo/'.$vehicleNo, 'get');
    }

    /**
     * 统计按天分组
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @return string
     */
    public function statByDayGroup($companyId,$startTime,$endTime){
        return $this->getResult("/DrivingMonitor/statByDayGroup/companyId/".$companyId."/startTime/".$startTime."/endTime/".$endTime,'get');
    }

    /**
     * 统计按小时分组
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @return string
     */
    public function statByTimeGroup($companyId,$startTime,$endTime){
        return $this->getResult("/DrivingMonitor/statByTimeGroup/companyId/".$companyId."/startTime/".$startTime."/endTime/".$endTime,'get');
    }

    /**
     * 所有司机行为类型统计
     * @param $startTime
     * @param $endTime
     * @param $company_id
     * @return string
     */
    public function showTiredType($startTime,$endTime,$company_id)
    {
        $result = $this->getResult("/DrivingMonitor/showTiredType/startTime/$startTime/endTime/$endTime/companyId/$company_id",'get');
        return $result;
    }

    /**
     * 指定司机行为类型统计
     * @param $startTime
     * @param $endTime
     * @param $company_id
     * @param $driverId
     * @return string
     */
    public function driverTiredType($startTime,$endTime,$company_id,$driverId)
    {
        $result = $this->getResult("/DrivingMonitor/showTiredType/startTime/$startTime/endTime/$endTime/companyId/$company_id/driverId/$driverId",'get');
        return $result;
    }

    /**
     * 司机报警次数统计
     * @param $startTime
     * @param $endTime
     * @param $company_id
     * @param $driverId
     * @return string
     */
    public function driverTiredNumber($startTime,$endTime,$company_id,$driverId)
    {
        $result = $this->getResult("/DrivingMonitor/statByDayGroup/startTime/$startTime/endTime/$endTime/companyId/$company_id/driverId/$driverId",'get');
        return $result;
    }


    /**
     * 疲劳值
     * @param $data
     * @return string
     */
    public function showTiredValue($data)
    {
        $result = $this->getResult("/DrivingMonitor/statTiredValue/",'post',$data);
        return $result;
    }

    /**
     * 实时报警次数
     * @param $companyId
     * @param $startTime
     * @return string
     */
    public function statTiredNo($companyId,$startTime)
    {
        $result = $this->getResult("/DrivingMonitor/statTiredNo/companyId/$companyId/startTime/$startTime",'get');
        return $result;
    }

    /**
     * 车辆坐标
     * @param $data
     * @return string
     */
    public function showVehicle($data)
    {
        $result = $this->getResult("/DrivingMonitor/getVehiclePosition",'post',$data);
        return $result;
    }

    /**
 * 导出行为图片
 * author 李文起
 * @param $companyId
 * @param $startTime
 * @param $endTime
 * @param $deviceNos
 * @param $code
 * @return string
 */
    public function exportBehaviorImages($companyId,$startTime,$endTime,$deviceNos,$code){
        $result = $this->getResult("/DrivingMonitor/exportBehaviorImages/companyId/".$companyId."/startTime/".$startTime."/endTime/".$endTime.'/deviceNos/'.$deviceNos.'/code/'.$code);
        return $result;
    }

    /**
     * 导出行为图片
     * author 李文起
     * @return string
     */
    public function checkPullAll(){
        $result = $this->getResult("/DrivingMonitor/checkPullAll");
        return $result;
    }

}