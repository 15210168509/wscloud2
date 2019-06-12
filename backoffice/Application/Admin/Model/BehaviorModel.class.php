<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/27
 * Time: 17:49
 */

namespace Admin\Model;


class BehaviorModel extends ApiModel
{
    /**
     * 行为列表
     * author 李文起
     * @param $para
     * @return string
     */
    public function getList($para){

        $url = '/DrivingMonitor/getBehaviorLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }
}