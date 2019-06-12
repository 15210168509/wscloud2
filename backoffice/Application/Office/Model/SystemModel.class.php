<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/12
 * Time: 16:13
 */

namespace Office\Model;


class SystemModel extends ApiModel
{
    /**
     * 获取预警类型
     * author 李文起
     * @param $companyId
     * @return string
     */
    public function getWarningType($companyId){
        return $this->getResult("/System/getWarningType/companyId/$companyId");
    }

    /**
     * 获取设置类型值
     * author 李文起
     * @param $companyId
     * @param $type
     * @return string
     */
    public function getSystemSetting($companyId,$type){
        return $this->getResult("/System/getSystemSetting/companyId/$companyId/type/$type");
    }

    /**
     * 设置预警类型
     * author 李文起
     * @param $data
     * @return string
     */
    public function setSystemSetting($data){
        return $this->getResult("/System/setSystemSetting/",'post',$data);
    }
}