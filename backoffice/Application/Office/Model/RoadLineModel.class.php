<?php
namespace Office\Model;

class RoadLineModel extends ApiModel
{
    public function addRoadLine($data)
    {
        $result = $this->getResult("/RoadLine/addRoadLine", 'post',$data);
        return $result;
    }

    public function searchVehicle($keywords,$companyId)
    {
        $result = $this->getResult("/RoadLine/searchVehicle/keywords/$keywords/companyId/$companyId", 'get');
        return $result;
    }

    /**
     * 获取路线
     * @param $companyId
     * @return string
     */
    public function getRoad($companyId)
    {
        $result = $this->getResult("/RoadLine/getRoad/companyId/$companyId", 'get');
        return $result;
    }

    /**
     * 路线信息
     * @param $roadId
     * @return string
     */
    public function getRoadLineInfo($roadId)
    {
        $result = $this->getResult("/RoadLine/getRoadLineInfo/roadId/$roadId", 'get');
        return $result;
    }

    /**
     * 获取车辆坐标
     * @param $deviceNo
     * @return string
     */
    public function getVehicleLocation($deviceNo)
    {
        $result = $this->getResult("/RoadLine/getVehicleLocation/deviceNo/$deviceNo", 'get');
        return $result;
    }

    public function getVehicleHistoryPoint($startTime,$endTime,$deviceNo)
    {
        $result = $this->getResult("/RoadLine/getVehicleHistoryPoint/startTime/$startTime/endTime/$endTime/deviceNo/$deviceNo", 'get');
        return $result;
    }

}