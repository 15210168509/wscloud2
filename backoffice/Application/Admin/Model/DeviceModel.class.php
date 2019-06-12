<?php
namespace Admin\Model;

class DeviceModel extends ApiModel
{
    public function getList($para){
        $url = '/Device/deviceLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }
}