<?php
namespace Office\Controller;
use Lib\BaseManagementController;
use Lib\Code;
use Lib\CommonConst;
use Think\Controller;
class IndexController extends BaseManagementController  {
    /**
     * 首页控制面板
     */
    public function Index()
    {
        $this->breadcrumb = array("控制面板"=>'/');
        $model = D('Admin');
        $res = $model->checkStatus($this->context->loginuser->company_id);

        if ($res['code']==Code::OK && $res['data']['verify_status'] != CommonConst::VERIFY_STATUS_OK) {
            $this->addJS(array('office/admin/verify.js'));
            $this->assign('company',$res['data']);
            $this->display('verify');
        } else {

            $startTime = time() - 3600 * 30 * 24;
            $endTime = time();
            $model = D('Index');
            $res = $model->statBehavior($this->context->loginuser->company_id,$startTime,$endTime);

            $warningHtml = '';
            if ($res['code'] == Code::OK){
                $warningHtml .= '<ul class="layui-row layui-col-space10 layui-this">';
                foreach ($res['data'] as $key=>$value){
                    $warningHtml .= '<li class="layui-col-xs4">     
                                        <span class="layadmin-backlog-body">
                                        <h3>'.$value['code_text'].'</h3> 
                                        <p><cite>'.$value['count'].'</cite></p>                          
                                    </span></li>';
                }
                $warningHtml .= '</ul>';
            }
            $this->assign('warningHtml',$warningHtml);
            $this->assign('warningInfo',$res['data']);
            $this->addCSS(array('office/index/index.css'=>'all'));
            $this->addJS(array('office/index/index.js','office/monitoring/echarts.js'));
            $this->display();
        }

    }

}