<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/4/26
 * Time: 11:54
 */

namespace Lib\Login;


use Lib\Auth;
use Lib\Msg;
use Lib\StatusCode;

class BuildToken extends AbstractLogin
{
    /**
     * 用户名密码登录
     * author 李文起
     * @param $account
     * @param $password
     * @param $type
     */
    public function loginByAccount($account,$password,$type) {
        $this->login->loginByAccount($account,$password,$type);
    }


    /**
     * 用户名密码登录
     * author 李文起
     * @param $account
     * @param $pwd
     */
    protected function doSelectByAccount($account, $pwd)
    {
        $result = $this->login->doSelectByAccount($account,$pwd);
        $this->buildToken($result);
    }



    /**
     * 创建build
     * author 李文起
     * @param $result
     */
    private function buildToken($result) {
        if (!empty($result)) {
            //获取token和refreshToken
            $auth   = new Auth();
            $token  = $auth->generateTokenById($result['id'],$this->getContext()->get(AbstractLogin::LOGIN_TYPE));

            $res = $this->login->setRefreshTokenToDB($result,$token);

            if ($res == false){
                $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,false);
                $this->getContext()->setError(Msg::CREATE_TOKEN_ERROR);
                $this->getContext()->setStatusCode(StatusCode::CREATE_TOKEN_ERROR);
            } else {
                $result = array_merge($result,$token);
                unset($result['id']);               //去掉userId
                $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,true);
                $this->getContext()->setParams(AbstractLogin::LOGIN_INFO,$result);
            }
        }
    }
}