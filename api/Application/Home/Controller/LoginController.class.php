<?php
namespace Home\Controller;
use Lib\Code;
use Lib\CommonConst;
use Lib\Login\AbstractLogin;
use Lib\Login\CompanyPackageObserver;
use Lib\Login\ManagerLogin;
use Lib\Login\BuildToken;
use Lib\LogOut\ManagerLogOut;
use Lib\RefreshToken\AdminRefreshToken;
use Lib\StatusCode;
use Lib\Login\OfficeLogin;
use Lib\Login\SecurityMonitorObserver;
use Lib\LogOut\ClearLoginInfoObserver;
use Lib\LogOut\GeneralLogOutLoggerObserver;
use Lib\LogOut\LogOut;
use Lib\LogOut\OfficeLogOut;
use Lib\Msg;

/**
 * Created by 李文起
 * User: 01
 * Date: 2018/4/17
 * Time: 15:09
 */
class LoginController extends AdvancedRestController
{
    public function index(){
        die('接口，禁止直接访问');
    }

    /**
     * 管理平台统一登录接口
     * @method get
     * @param $account
     * @param $type
     * @param $password
     * @return mixed json格式 status msg data
     */
    public function login($type, $account, $password)
    {
        if(empty($type) || empty($account) || empty($password)){
            //验证参数是否为空
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }else{
            //密码加密
            $password = md5($password);

            //判断登录用户类型，加载对应的数据表就行查询
            switch ($type) {
                //管理员登录
                case CommonConst::PC_ADMIN :
                    $login = new BuildToken(new OfficeLogin());
                    $login->loginByAccount($account,$password,$type);
                    $this->loginMonitorObserver($login);
                    break;

                //管理员登录
                case CommonConst::PC_MANAGER :
                    $login = new BuildToken(new ManagerLogin());
                    $login->loginByAccount($account,$password,$type);
                    $this->loginMonitorObserver($login);
                    break;

                default:
                    $this->setReturnVal(Code::ERROR, Msg::LOGIN_TYPE_ERROR, StatusCode::LOGIN_TYPE_ERROR,array($type));
            }

        }
        $this->restReturn();
    }



    /**
     * 安全判断操作
     * @param $login object  登录端类型
     */
    public function loginMonitorObserver($login){
        //添加观察者：登录安全判断
        new SecurityMonitorObserver($login);
        //检测公司套餐
        new CompanyPackageObserver($login);

        //登录逻辑触发
        $login->handleLogin();
        $loginContext = $login->getContext();
        if ($loginContext->get(AbstractLogin::LOGIN_LABEL)){
            $this->setReturnVal(Code::OK,Msg::LOGIN_SUCCESS,StatusCode::LOGIN_SUCCESS,$loginContext->get(AbstractLogin::LOGIN_INFO));
        } else {
            $this->setReturnVal(Code::ERROR,$loginContext->getError(),$loginContext->getStatusCode());
        }
    }

    /**
     * 用refresh更新token
     * @param $refreshToken $string 刷新token
     * @param $token string 旧有token
     * @param $type string  退出类型
     * @ignore
     */
    public function refreshToken($refreshToken,$token,$type = CommonConst::APP_USER_ANDROID){

        $result = false;
        switch ($type) {
            //管理员刷新token
            case CommonConst::PC_ADMIN :
            case CommonConst::H5_ADMIN :
                $this->refreshTokenValue(new AdminRefreshToken($token,$refreshToken));
                break;

            default:
                $this->setReturnVal(Code::ERROR, Msg::LOGIN_TYPE_ERROR, StatusCode::LOGIN_TYPE_ERROR,array($type));
        }
        $this->restReturn();
    }

    /**
     * 刷新token
     * author 李文起
     * @param $refresh
     */
    private function refreshTokenValue($refresh){
        $result = $refresh->refreshToken();
        if (!$result) {
            $this->setReturnVal(Code::ERROR,Msg::TOKEN_REFRESH_ERROR,StatusCode::TOKEN_REFRESH_ERROR);
        } else {
            $this->setReturnVal(Code::OK,Msg::TOKEN_REFRESH_SUCCESS,StatusCode::TOKEN_REFRESH_SUCCESS,$result);
        }
    }

    /**
     * 用户退出
     * author 李文起
     * @param $type
     */
    public function logOut($type){
        if(empty($type)){
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        } else {
            switch ($type){
                //管理员退出
                case CommonConst::PC_ADMIN :
                    $this->logoutMonitorObserver(new OfficeLogOut($_GET['adminId'],$type));
                    break;

                //管理员退出
                case CommonConst::PC_MANAGER :
                    $this->logoutMonitorObserver(new ManagerLogOut($_GET['managerId'],$type));
                    break;

                default :
                    $this->setReturnVal(Code::ERROR, Msg::LOGIN_TYPE_ERROR, StatusCode::LOGIN_TYPE_ERROR,array($type));
            }
        }
        $this->restReturn();
    }


    /**
     * 用户退出
     * author 李文起
     * @param $logout
     */
    private function logoutMonitorObserver($logout){
        // 添加观察者：清除用户登录信息
        new ClearLoginInfoObserver($logout);

        // 添加观察者：记录信息日志
        new GeneralLogOutLoggerObserver($logout);

        // 退出逻辑触发
        $logout->handleLogOut();
        $logOutContext = $logout->getContext();
        if ($logOutContext->get(LogOut::LOGOUT_LABEL)){
            $this->setReturnVal(Code::OK, Msg::LOGOUT_SUCCESS,StatusCode::LOGOUT_SUCCESS);
        } else {
            $this->setReturnVal(Code::ERROR, $logOutContext->getError(),$logOutContext->getStatusCode());
        }
    }
}