<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Admin\Controller;

use Lib\Code;
use Lib\ListAdminController;
use Lib\Msg;

class DeviceController extends ListAdminController
{

    public $authentication = true;

    /**
     * 设备列表
     * @param $companyId
     * @param $company
     */
    public function lists($companyId='null',$company='null')
    {
        $this->breadcrumb = array("首页"=>'/', "设备管理"=>"#", "设备列表"=>"#");
        $this->addJS(array('admin/device/deviceList.js'));

        // 列表检索条件
        $session_para = $this->getSessionParam('Device');

        if (false !== $session_para) {
            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }

            if ($session_para['serialNo'] != 'null' && !empty($session_para['serialNo'])) {
                $this->assign('where_serialNo', $session_para['serialNo']);
            } else {
                $this->assign('where_serialNo', '');
            }

            if ($session_para['companyId'] != 'null' && !empty($session_para['companyId'])) {
                $this->assign('where_companyId', $session_para['companyId']);
            } else {
                $this->assign('where_companyId', $companyId == 'null'? '':$companyId);
            }

            if ($session_para['company'] != 'null' && !empty($session_para['company'])) {
                $this->assign('where_company', $session_para['company']);
            } else {
                $this->assign('where_company', $company == 'null' ?  '':$company);
            }
        }
        $this->display('deviceList');
    }

    /**
     * 搜索员工
     */
    public function search()
    {
        $para = array('name'=>'string','serialNo'=>'string','companyId'=>'string','company'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Device','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        return $list;
    }
}