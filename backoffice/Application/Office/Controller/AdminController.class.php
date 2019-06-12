<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Office\Controller;

use Lib\BaseManagementController;
use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;
use Lib\Status;
use Lib\StatusCode;
use Lib\Tools;

class AdminController extends ListManagementController
{

    public $authentication = true;

    /**
     * 添加管理员
     */
    public function add()
    {
        $this->breadcrumb = array("管理员管理"=>'#','添加管理员'=>'add');
        $this->addJS(array('office/admin/addAdmin.js'));
        $right_html = rightHtml();
        $this->assign('rightHtml',$right_html);
        $this->display();
    }

    public function ajaxAddAdmin()
    {
        $data['id'] = Tools::generateId();
        $data['company_id'] = $this->context->loginuser->company_id;
        $data['name'] = I('post.name');
        $data['account'] = I('post.account');
        $data['phone'] = I('post.phone');
        $data['email'] = I('post.email');
        $data['password'] = md5(I('post.password'));
        if (I('post.right')) {
            $data['right'] = implode(',', I('post.right'));
        } else {
            $data['right'] = '';
        }
        $model = D('Admin');
        $res = $model->ajaxAddAdmin($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 管理员列表
     */
    public function lists()
    {
        $this->breadcrumb = array("首页"=>'/', "管理员管理"=>"#", "管理员列表"=>"#");
        $this->addJS(array('office/admin/adminList.js'));

        // 列表检索条件
        $session_para = $this->getSessionParam('Admin');

        if (false !== $session_para) {
            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }
            if ($session_para['phone'] != 'null' && !empty($session_para['phone'])) {
                $this->assign('where_phone', $session_para['phone']);
            } else {
                $this->assign('where_phone', '');
            }
            if ($session_para['status'] != 'null' && !empty($session_para['status'])) {
                $this->assign('where_status', $session_para['status']);
            } else {
                $this->assign('where_status', '');
            }
        }
        $this->assign('companyId',$this->context->loginuser->company_id);
        $this->display('adminList');
    }

    /**
     * 搜索员工
     */
    public function search()
    {
        $para = array('name'=>'string','phone'=>'string','status'=>'int','companyId'=>'string');
        $this->setFilterCondition($para);
        $list = $this->getList('Admin','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {

                $value->action .= '<a href="adminDetail/adminId/'.$value->id.'" class="layui-btn layui-btn-xs">详情</a>';
                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del" data="'.$value->id.'">删除</button>';

            }
        }
        return $list;
    }

    /**
     * 管理员详情
     * @param $adminId
     */
    public function adminDetail($adminId)
    {
        $this->breadcrumb = array("管理员管理"=>'#','管理员详情'=>'#');
        $right_html = rightHtml();
        $this->assign('rightHtml',$right_html);
        $this->assign('admin_id',$adminId);
        $this->addJS(array('office/admin/adminDetail.js'));
        $this->display();
    }

    public function adminFind($adminId)
    {
        $model = D('Admin');
        $admin = $model->adminDetail($adminId);
        $this->ajaxReturn(array('code'=>$admin['code'],'msg'=>$admin['msg'],'data'=>$admin['data']));
    }

    /**
     * 修改管理员信息
     */
    public function ajaxEditAdmin()
    {
        $data['id'] = I('post.id');
        $data['name'] = I('post.name');
        $data['account'] = I('post.account');
        $data['phone'] = I('post.phone');
        $data['email'] = I('post.email');
        $data['company_id'] = $this->context->loginuser->company_id;
        if (I('post.right')) {
            $data['right'] = implode(',', I('post.right'));
        } else {
            $data['right'] = '';
        }
        $model = D('Admin');
        $res = $model->ajaxEditAdmin($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 重置管理员密码
     * @param $adminId
     */
    public function resetAdminPWD($adminId)
    {
        $model = D('Admin');
        $res = $model->resetAdminPassword($adminId);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 删除管理员
     * @param $adminId
     */
    public function delAdmin($adminId)
    {
        $model = D('Admin');
        $res = $model->delAdmin($adminId);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 管理员消息
     */
    public function adminMsg()
    {
        $this->breadcrumb = array("首页"=>'/', "消息列表"=>"#");
        $this->addJS(array('office/admin/adminMsgList.js'));

        // 列表检索条件
        $session_para = $this->getSessionParam('AdminMsg');

        if (false !== $session_para) {
            if ($session_para['status'] != 'null' && !empty($session_para['status'])) {
                $this->assign('where_status', $session_para['status']);
            } else {
                $this->assign('where_status', '');
            }
            if ($session_para['startTime'] != 'null' && !empty($session_para['startTime'])) {
                $this->assign('where_startTime', $session_para['startTime']);
            } else {
                $this->assign('where_startTime', '');
            }
            if ($session_para['endTime'] != 'null' && !empty($session_para['endTime'])) {
                $this->assign('where_endTime', $session_para['endTime']);
            } else {
                $this->assign('where_endTime', '');
            }
        }
        $this->display('adminMsgList');
    }

    /**
     * 搜索员工
     */
    public function msgSearch()
    {
        $para = array('startTime'=>'stime','endTime'=>'etime','status'=>'int');
        $this->setFilterCondition($para);
        $list = $this->getList('AdminMsg','formatMsgList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatMsgList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                if ($value->status == Status::USER_MSG_UNREAD) {
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="read">查阅</button>';
                }

                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del">删除</button>';
            }
        }
        return $list;
    }

    public function readMsg($id)
    {
        $model = D('AdminMsg');
        $res = $model->readMsg($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    public function delMsg($id)
    {
        $model = D('AdminMsg');
        $res = $model->delMsg($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 修改公司信息
     */
    public function saveCompanyInfo()
    {
        $data['name'] = I('post.name');
        $data['phone'] = I('post.phone');
        $data['email'] = I('post.email');
        $data['id'] = $this->context->loginuser->company_id;
        $model = D('Admin');
        $res = $model->saveCompanyInfo($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 修改个人信息
     * author 李文起
     */
    public function adminInfo(){

        $this->breadcrumb = array("首页"=>'/', "个人信息修改"=>"#");
        $this->addJS('office/admin/adminInfo.js');

        $this->assign('adminInfo',$this->context->loginuser);
        $this->display();
    }

    /**
     * 修改个人信息
     * author 李文起
     */
    public function ajaxUpdateAdminInfo(){
        $data['name'] = I('post.name');
        $data['account'] = I('post.account');
        $data['phone'] = I('post.phone');
        $data['email'] = I('post.email');
        $data['company_id'] = $this->context->loginuser->company_id;

        if (isset($data['name']) && isset($data['account']) && isset($data['phone']) && isset($data['email'])) {
            if (I('post.oldPassword') != ''){
                $data['oldPassword']    = I('post.oldPassword');
                $data['newPassword']    = I('post.newPassword');
            }

            $model = D('Admin');
            $res = $model->updateAdminInfo($data);

            if ($res['code'] == Code::OK){
                $this->context->addKey('name',$data['name']);
                $this->context->addKey('account',$data['account']);
                $this->context->addKey('phone',$data['phone']);
                $this->context->addKey('email',$data['email']);
            }

            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
        } else {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING,'status_code'=>StatusCode::PARA_MISSING));
        }
    }

}