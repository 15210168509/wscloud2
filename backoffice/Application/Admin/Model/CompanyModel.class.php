<?php
namespace Admin\Model;

/**
 * Class LoginModel
 * @package Home\Model
 */
class CompanyModel extends ApiModel
{
    public function getList($para){
        $url = '/Company/lists/';
        foreach($para as $key=>$value){
            $url .= $key.'/'.$value.'/';
        }
        $result = $this->getResult($url.rtrim('/'));
        return $result;
    }

    /**
     * 删除
     * @param $id
     * @return string
     */
    public function delCompany($id)
    {
        return $this->getResult("/Company/delCompany/id/".$id, "get");
    }

    /**
     * 审核公司
     * @param $data
     * @return string
     */
    public function verifyCompany($data)
    {
        return $this->getResult("/Company/verifyCompany", "post",$data);
    }

    /**
     * 设置套餐
     * @param $data
     * @return string
     */
    public function setCompanyPackage($data)
    {
        return $this->getResult("/Company/setCompanyPackage", "post",$data);
    }

    /**
     * 公司详情
     * @param $id
     * @return string
     */
    public function getCompanyInfo($id)
    {
        return $this->getResult("/Company/getCompanyInfo/id/$id", "get");
    }

    /**
     * 预警信息
     * @param $companyId
     * @return string
     */
    public function statBehavior($companyId){
        $result = $this->getResult("/Company/statBehavior/companyId/".$companyId, 'get');
        return $result;
    }

    /**
     * 套餐信息
     * @param $id
     * @return string
     */
    public function getPackageInfo($id)
    {
        return $this->getResult("/Company/getPackageInfo/id/$id", "get");
    }

    /**
     * author 李文起
     * @param $id
     * @return string
     */
    public function baseDataStats($id){
        return $this->getResult("/CompanyStats/baseDataStats/companyId/$id", "get");
    }
    /**
     * 添加公司
     * @param $data
     * @return string
     */
    public function ajaxAddCompany($data)
    {
        return $this->getResult("/Company/ajaxAddCompany", "post",$data);
    }

}
?>