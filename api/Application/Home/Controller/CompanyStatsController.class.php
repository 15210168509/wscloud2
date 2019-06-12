<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/27
 * Time: 14:50
 */

namespace Home\Controller;


use Lib\Code;
use Lib\Msg;
use Lib\StatusCode;

class CompanyStatsController extends AdvancedRestController
{

    /**
     * 获取公司基本数据统计
     * author 李文起
     * @param $companyId
     */
    public function baseDataStats($companyId){
        $deviceData  = $this->deviceDataStats($companyId);
        $driverData  = $this->driverDataStats($companyId);
        $vehicleData = $this->vehicleDataStats($companyId);

        $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,array_merge($deviceData,$driverData,$vehicleData));
        $this->restReturn();
    }

    /**
     * 设备数据统计
     * author 李文起
     * @param $companyId
     * @return array
     */
    private function deviceDataStats($companyId){
        $model = D('Device');

        $map['company_id']  = $companyId;

        $deviceData = array('device_disable'=>0,'device_enable'=>0);

        $res = $model->field(array('case when del_flg=0 then "device_enable" else "device_disable" end  as del_flg', 'count(1) count'))
            ->where($map)
            ->group('del_flg')
            ->select();

        foreach ($res as $key=>$value) {
            if (array_key_exists($value['del_flg'], $deviceData)) {
                $deviceData[$value['del_flg']] = $value['count'];
            }
        }

        return $deviceData;

    }

    /**
     * 司机数据统计
     * author 李文起
     * @param $companyId
     * @return array
     */
    private function driverDataStats($companyId){
        $model = D('Driver');

        $map['company_id']  = $companyId;

        $driverData = array('driver_disable'=>0,'driver_enable'=>0);

        $res = $model->field(array('case when del_flg=0 then "driver_enable" else "driver_disable" end  as del_flg', 'count(1) count'))
            ->where($map)
            ->group('del_flg')
            ->select();

        foreach ($res as $key=>$value) {
            if (array_key_exists($value['del_flg'], $driverData)) {
                $driverData[$value['del_flg']] = $value['count'];
            }
        }

        return $driverData;
    }

    /**
     * 车辆数据统计
     * author 李文起
     * @param $companyId
     * @return array
     */
    private function vehicleDataStats($companyId){
        $model = D('Vehicle');

        $map['company_id']  = $companyId;

        $vehicleData = array('vehicle_disable'=>0,'vehicle_enable'=>0);

        $res = $model->field(array('case when del_flg=0 then "vehicle_enable" else "vehicle_disable" end  as del_flg', 'count(1) count'))
            ->where($map)
            ->group('del_flg')
            ->select();

        foreach ($res as $key=>$value) {
            if (array_key_exists($value['del_flg'], $vehicleData)) {
                $vehicleData[$value['del_flg']] = $value['count'];
            }
        }

        return $vehicleData;
    }
}