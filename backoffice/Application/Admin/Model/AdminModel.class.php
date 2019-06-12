<?php
namespace Admin\Model;

class AdminModel extends ApiModel
{
    public function getList($para){
        $url = '/Manager/managerLists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }
    /**
     * 获取所有员工
     */
    public function getAdminAll()
    {
        $result = $this->getResult("/Manager/getAdminAll", 'get');
        return $result;
    }

    /**
     * 添加管理员
     * @param $data
     * @return string
     */
    public function ajaxAddAdmin($data)
    {
        $result = $this->getResult("/Manager/ajaxAddManager", 'post',$data);
        return $result;
    }

    /**
     * 管理员详情
     * @param $adminId
     * @return string
     */
    public function adminDetail($adminId)
    {
        $result = $this->getResult("/Manager/managerDetail/manager_id/$adminId", 'get');
        return $result;
    }

    /**
     * 修改管理员信息
     * @param $data
     * @return string
     */
    public function ajaxEditAdmin($data)
    {
        $result = $this->getResult("/Manager/ajaxEditManager", 'post',$data);
        return $result;
    }

    /**
     * 重置密码
     * @param $adminId
     * @return string
     */
    public function resetAdminPassword($adminId)
    {
        $result = $this->getResult("/Manager/resetManagerPassword/id/$adminId", 'get');
        return $result;
    }

    /**
     * 删除管理员
     * @param $adminId
     * @return string
     */
    public function delAdmin($adminId)
    {
        $result = $this->getResult("/Manager/delManager/id/$adminId", 'get');
        return $result;
    }

    /**
     * 修改个人信息
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateAdminInfo($data){
        $result = $this->getResult("/Manager/updateManagerInfo", 'post',$data);
        return $result;
    }

}