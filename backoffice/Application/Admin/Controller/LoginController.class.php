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
use Lib\Tools;

class LoginController extends ListAdminController
{

    public $authentication = false;

    public function index()
    {
        $this->assign('baseUrl', C('baseUrl'));
        $this->display();
    }

    public function loginCheck()
    {
        //获取参数
        $para = array(
            "account" => isset($_POST["login_account"]) ? trim($_POST["login_account"]) : "",
            "pass" => isset($_POST["login_pass"]) ? trim($_POST["login_pass"]) : "",
            "keep" => isset($_POST["login_keep"]) && $_POST["login_keep"] == "true" ? true : false
        );
        $_res = new \stdClass();
        //参数验证
        $bo = true;
        if($bo && $para["account"] == ""){
            $_res->res = false;
            $_res->msg = "登陆账户不能为空";
            $_res->code = "account";
            $bo = false;
        }
        if($bo && $para["pass"] == ""){
            $_res->res = false;
            $_res->msg = "登陆密码不能为空";
            $_res->code = "pass";
            $bo = false;
        }

        if($bo){

            //验证登陆

            $oa = D('Login');
            $_res = $oa->login($para["account"], $para["pass"]);

            if($_res->res){
                //创建session
                $this->context->createSession($_res->data);
                if($para["keep"]) {

                    $this->makeCookie($para);
                } else {

                    $this->dropCookie();
                }
            }
        }

        //返回
        if(IS_AJAX){
            $this->ajaxReturn($_res, "json");
        }
        else{
            $this->assign("res", $_res);
            $this->display();
        }
    }

    /**
     * 保存登陆cookie
     * @param $account object 用户登录信息
     */

    public function makeCookie($account){
        $va = new \stdClass();
        $va->account = $account["account"];
        $va->pass = $account["pass"];
        $va = serialize($va);
        $des = new Des();
        $va = $des->encrypt($va, TCTConst::LOGIN_DES_KEY, true);
        $date = strtotime("1 month");
        setCookie("tct", $va, $date, '/');
    }

    /**
     * 删除登陆cookie
     */
    public function dropCookie(){
        setCookie("tct", "", time()-3600);
    }

    /**
     * 写入session
     * @param $data
     */
    public function makeSession($data){
        $_SESSION['login'] = $data[0];
    }


    /**
     * 用户注销
     */
    public function logout(){
        $model = D('ManagementLogin');
        $admin_id = session(C('OfficeSessionKey'))->id;

        $result = $model->logOut($admin_id);

        if($result->code ==CommonConst::API_CODE_SUCCESS){

        }else{

        }

        if(isset($_COOKIE[session_name()]))
        {
            setCookie(session_name(),'',time()-3600,'/');
        }
        session_destroy();

        $this->dropCookie();
        if(IS_AJAX){
            $this->ajaxReturn(array("res" => true, "msg" => "已退出"));
        }
        else
        {
            redirect('/Office/ManagementLogin/index');
        }
    }

    public function session(){
        dump($_SESSION);
    }

    /**
     * 发送验证码
     */
    public function ajaxSendMobileVerificationCode($phone)
    {
        if (Tools::isEmpty($phone)) {
            $this->ajaxReturn(array('code'=>CommonConst::CODE_ERROR,'msg'=>Msg::PHONE_REQUIRED));
        } else if (!Tools::checkPhone($phone)) {
            $this->ajaxReturn(array('code'=>CommonConst::CODE_ERROR,'msg'=>Msg::PHONE_INVALID));
        } else {
            $model = D('ManagementLogin');
            $result = $model->sendMobileVerificationCode($phone);
            if ($result['code'] == CommonConst::API_CODE_SUCCESS) {
                $this->ajaxReturn(array('code'=>CommonConst::CODE_SUCCESS,'msg'=>$result['msg']));
            } else {
                $this->ajaxReturn(array('code'=>CommonConst::CODE_ERROR,'msg'=>$result['msg']));
            }
        }
    }

    /**
     * 验证手机验证码
     * @param $phone
     * @param $verificationCode
     * @return bool
     */
    public function ajaxValidateMobileVerificationCode($phone,$verificationCode)
    {
        $model = D('ManagementLogin');
        $res = $model->ajaxValidateMobileVerificationCode($phone,$verificationCode,1);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(true);
        } else {
            $this->ajaxReturn(false);
        }
    }

    /**
     *注册公司
     */
    public function registerCompany()
    {
        $data['id'] = Tools::generateId();
        $data['name'] = I('post.registerCompanyName');
        $data['phone'] = I('post.registerCompanyContactPhone');
        $data['email'] = I('post.email');
        $data['right'] = getRightAll();
        $model = D('ManagementLogin');
        $res = $model->registerCompany($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }
}