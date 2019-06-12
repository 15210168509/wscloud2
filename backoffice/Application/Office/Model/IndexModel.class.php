<?php
namespace Office\Model;

class IndexModel extends ApiModel
{
    /**
     * 统计30天内的报警次数
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @return string
     */
    public function statBehavior($companyId,$startTime,$endTime){
        $result = $this->getResult("/DrivingMonitor/statBehavior/companyId/".$companyId."/startTime/".$startTime."/endTime/".$endTime, 'get');
        return $result;
    }

}