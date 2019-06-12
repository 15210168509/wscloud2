<?php
namespace Admin\Model;

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

    /**
     * 检索公司
     * author 李文起
     * @param $keyword
     * @return string
     */
    public function searchCompany($keyword)
    {
        return $this->getResult("/Company/searchCompany/keyword/$keyword");
    }
}