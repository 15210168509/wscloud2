<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/12
 * Time: 15:27
 */

namespace Office\Controller;


use Lib\CommonConst;
use Lib\ListManagementController;

class SystemController extends ListManagementController
{
    /**
     * 系统预警设置
     * author 李文起
     */
    public function index(){

        $this->breadcrumb = array("系统设置"=>'#','预警设置'=>'add');
        $this->addJS(array('office/system/warningSetting.js'));

        $model = D('System');

        //预警类型
        $res = $model->getWarningType($this->context->loginuser->company_id);
        //预警设置值
        $resWarning = $model->getSystemSetting($this->context->loginuser->company_id,CommonConst::SYSTEM_SET_WARNING);
        $html = $this->warningSelect($res['data'],explode(',',$resWarning['data']['value']));
        $this->assign('warningSetting',$html);

        //弹出框设置
        $resWarningDialog = $model->getSystemSetting($this->context->loginuser->company_id,CommonConst::SYSTEM_SET_WARNING_DIALOG);
        if ($resWarningDialog['data']['value'] == '1'){
            $this->assign('warningDialogPop','checked=""');
        } else {
            $this->assign('warningDialogPop','');
        }
        $this->display();
    }

    /**
     * 预警设置html
     * author 李文起
     * @param $warningType
     * @param $warningSetting
     * @return string
     */
    private function warningSelect($warningType,$warningSetting){

        $html = '<div  class="layui-form-item"><input type="checkbox" lay-filter="selectAll" id="selectAll" lay-skin="primary" title="全选"></div>';

        foreach ($warningType as $key => $value) {
            $html .= '<div  class="layui-form-item"><label class="layui-form-label">'.$key.'</label>';

            $html .= '<div class="layui-input-block"><br/>';
            foreach ($value as $k=>$v){
                $checked = in_array($k,$warningSetting)? 'checked=""':'';
                $html .= '<input type="checkbox" name="warningType[]" lay-skin="primary" lay-filter="select"  lay-verify="warningType" '.$checked.' title='.$v.' value="'.$k.'"/><br/>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * 预警设置
     * author 李文起
     */
    public function setWarning(){
        $model = D('System');

        $data['companyId']   = $this->context->loginuser->company_id;
        $data['type']        = CommonConst::SYSTEM_SET_WARNING;
        $data['value']       = implode(',',I('post.warningType'));

        $res = $model->setSystemSetting($data);

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
    }

    /**
     * 预警弹框设置
     * author 李文起
     */
    public function setWarningDialog(){
        $model = D('System');

        $data['companyId']   = $this->context->loginuser->company_id;
        $data['type']        = CommonConst::SYSTEM_SET_WARNING_DIALOG;
        $data['value']       = I('post.open') ? 1 :0;

        $res = $model->setSystemSetting($data);

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
    }
}