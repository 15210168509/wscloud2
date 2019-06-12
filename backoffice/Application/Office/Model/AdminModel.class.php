<?php
namespace Office\Model;

class AdminModel extends ApiModel
{
    public function getList($para){
        $url = '/Admin/adminLists/';
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
        $result = $this->getResult("/Admin/getAdminAll", 'get');
        return $result;
    }

    /**
     * 添加管理员
     * @param $data
     * @return string
     */
    public function ajaxAddAdmin($data)
    {
        $result = $this->getResult("/Admin/ajaxAddAdmin", 'post',$data);
        return $result;
    }

    /**
     * 管理员详情
     * @param $adminId
     * @return string
     */
    public function adminDetail($adminId)
    {
        $result = $this->getResult("/Admin/adminDetail/admin_id/$adminId", 'get');
        return $result;
    }

    /**
     * 修改管理员信息
     * @param $data
     * @return string
     */
    public function ajaxEditAdmin($data)
    {
        $result = $this->getResult("/Admin/ajaxEditAdmin", 'post',$data);
        return $result;
    }

    /**
     * 重置密码
     * @param $adminId
     * @return string
     */
    public function resetAdminPassword($adminId)
    {
        $result = $this->getResult("/Admin/resetAdminPassword/id/$adminId", 'get');
        return $result;
    }

    /**
     * 删除管理员
     * @param $adminId
     * @return string
     */
    public function delAdmin($adminId)
    {
        $result = $this->getResult("/Admin/delAdmin/id/$adminId", 'get');
        return $result;
    }

    /**
     * 获取公司审核信息
     * @param $companyId
     * @return string
     */
    public function checkStatus($companyId)
    {
        $result = $this->getResult("/Company/checkStatus/companyId/$companyId", 'get');
        return $result;
    }

    /**
     * 修改公司信息
     * @param $data
     * @return string
     */
    public function saveCompanyInfo($data)
    {
        $result = $this->getResult("/Company/saveCompanyInfo", 'post',$data);
        return $result;
    }

    /**
     * 修改个人信息
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateAdminInfo($data){
        $result = $this->getResult("/Admin/updateAdminInfo", 'post',$data);
        return $result;
    }

}