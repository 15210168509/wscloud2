<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Admin\Controller;

use Lib\ListAdminController;

class VehicleController extends ListAdminController
{
    /**
     * 车辆列表
     * @param $companyId
     * @param $company
     */
    public function lists($companyId='null',$company='null')
    {
        $this->breadcrumb = array("车辆管理"=>"#", "车辆列表"=>"lists");
        $this->addJS(array('admin/vehicle/vehicleList.js'));

        // 列表检索条件
        $session_para = $this->getSessionParam('Vehicle');

        if (false !== $session_para) {

            if ($session_para['vehicle_no'] != 'null' && !empty($session_para['vehicle_no'])) {
                $this->assign('where_vehicle_no', $session_para['vehicle_no']);
            } else {
                $this->assign('where_vehicle_no', '');
            }

            if ($session_para['companyId'] != 'null' && !empty($session_para['companyId'])) {
                $this->assign('where_companyId', $session_para['companyId']);
            } else {
                $this->assign('where_companyId', $companyId == 'null'? '':$companyId);
            }

            if ($session_para['company'] != 'null' && !empty($session_para['company'])) {
                $this->assign('where_company', $session_para['company']);
            } else {
                $this->assign('where_company',$company == 'null' ?  '':$company);
            }
        }
        $this->display('vehicleList');
    }

    /**
     * 搜索员工
     */
    public function search()
    {
        $para = array('vehicle_no'=>'string','companyId'=>'string','company'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Vehicle','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        return $list;
    }
}