<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 16:13
 */

namespace Admin\Model;


class DriverModel extends ApiModel
{
    /**
     * 获取司机列表
     * author 李文起
     * @param $para
     * @return string
     */
    public function getList($para){

        $url = '/Driver/driverLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }

}