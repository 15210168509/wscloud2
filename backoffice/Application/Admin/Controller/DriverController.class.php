<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 11:42
 */

namespace Admin\Controller;

use Lib\Code;
use Lib\CommonConst;
use Lib\ListAdminController;
use Lib\Logger\Logger;
use Lib\Msg;
use Lib\Ws\WsClient;

class DriverController extends ListAdminController
{
    public function index(){
        die('禁止访问');
    }

    /**
     * 司机列表
     * @param $companyId
     * @param $company
     */
    public function lists($companyId='null',$company='null')
    {
        $this->breadcrumb = array("司机管理"=>"/","司机列表"=>'/');


        // 列表检索条件
        $session_para = $this->getSessionParam('Driver');

        if (false !== $session_para) {

            if ($session_para['phone'] != 'null' && !empty($session_para['phone'])) {
                $this->assign('where_phone', $session_para['phone']);
            } else {
                $this->assign('where_phone', '');
            }

            if ($session_para['account'] != 'null' && !empty($session_para['account'])) {
                $this->assign('where_account', $session_para['account']);
            } else {
                $this->assign('where_account', '');
            }

            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }

            if ($session_para['status'] != 'null' && !empty($session_para['status'])) {
                $this->assign('where_status', $session_para['status']);
            } else {
                $this->assign('where_status', '');
            }

            if ($session_para['companyId'] != 'null' && !empty($session_para['companyId'])) {
                $this->assign('where_companyId', $session_para['companyId']);
            } else {
                $this->assign('where_companyId',  $companyId == 'null'? '':$companyId);
            }

            if ($session_para['company'] != 'null' && !empty($session_para['company'])) {
                $this->assign('where_company', $session_para['company']);
            } else {
                $this->assign('where_company', $company == 'null' ?  '':$company);
            }

        }

        $this->addJS("admin/driver/lists.js");
        $this->display();
    }

    /**
     * 司机列表
     * author 李文起
     */
    public function ajaxLists(){
        $para = array('name'=>'string','status'=>'int','phone'=>'string','account'=>'string','companyId'=>'string','company'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Driver','driverLists');
        $this->ajaxReturn($list,'json');
    }

    /**
     * 司机列表
     * author 李文起
     * @param $list
     * @return mixed
     */
    public function driverLists($list){
        return $list;
    }
}