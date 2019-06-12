<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Admin\Controller;

use Lib\Code;
use Lib\CommonConst;
use Lib\ListAdminController;
use Lib\Msg;
use Lib\Status;
use Lib\Tools;

class CompanyController extends ListAdminController
{

    public $authentication = true;

    /**
     * 公司列表
     */
    public function lists()
    {
        $this->breadcrumb = array("公司管理"=>'#','公司列表'=>'lists');
        $this->addJS(array('admin/company/companyLists.js'));
        // 列表检索条件
        $session_para = $this->getSessionParam('Company');

        if (false !== $session_para) {
            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }
        }
        $this->display('companyList');
    }


    public function search()
    {
        $para = array('name'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Company','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="package">套餐</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="info">详情</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del">删除</button>';
                if ($value->verify_status == Status::COMPANY_VERIFY_STATUS_ING) {
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="verify">审核</button>';
                }
            }
        }
        return $list;
    }

    /**
     * 删除公司
     * @param $id
     */
    public function delCompany($id)
    {
        $model = D('Company');
        $res = $model->delCompany($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 审核公司
     */
    public function verifyCompany()
    {
        $data['id'] = I('get.id');
        $data['verify_status'] = I('get.verifyStatus');
        $data['comment'] = I('get.comment');
        $model = D('Company');
        $res = $model->verifyCompany($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 公司详情
     * @param $id
     */
    public function companyInfo($id)
    {
        $this->breadcrumb = array("公司管理"=>'#','公司详情'=>'#');
        $this->addCSS(array('admin/index/index.css'=>'all'));
        $model = D('Company');
        $res = $model->getCompanyInfo($id);
        $this->assign('companyInfo',$res['data']);

        //报警监控数据
        $monitorRes = $model->statBehavior($id);
        $this->assign('monitor',$monitorRes['data']);

        //公司基本数据
        $res = $model->baseDataStats($id);
        $this->assign('baseData',$res['data']);

        $this->addJS(array('admin/company/companyInfo.js'));
        $this->display();
    }

    /**
     * 设置套餐
     */
    public function setCompanyPackage()
    {
        $data['company_id'] = I('get.id');
        $data['start_time'] = strtotime(I('get.start_time'));
        $data['end_time'] = strtotime(I('get.end_time'));
        $data['money'] = I('get.money')*100;
        $data['devices'] = I('get.devices');
        $model = D('Company');
        $res = $model->setCompanyPackage($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 公司套餐信息
     * @param $id
     */
    public function getPackageInfo($id)
    {
        $model = D('Company');
        $res = $model->getPackageInfo($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 添加公司
     */
    public function add()
    {
        $this->breadcrumb = array("公司管理"=>'#','添加公司'=>'add');
        $this->addJS('admin/company/add.js');
        $this->display('add');
    }

    /**
     * 添加公司
     */
    public function ajaxAddCompany()
    {
        $data['id'] = Tools::generateId();
        $data['name']       = I('post.name');
        $data['email']      = I('post.email');
        $data['phone']      = I('post.phone');
        $data['password']   = I('post.password');
        $model = D('Company');
        $res = $model->ajaxAddCompany($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

}