<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/8
 * Time: 10:09
 */

namespace Lib\Login;


use Lib\Login\AbstractLogin;
use Lib\Msg;
use Lib\StatusCode;

class LoginInstance extends AbstractLogin
{
    protected function doSelectByAccount($account, $pwd)
    {
        $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,false);
        $this->getContext()->setError(Msg::LOGIN_ERROR);
        $this->getContext()->setStatusCode(StatusCode::LOGIN_ERROR);
        return array();
    }

    protected function doSelectByFace($faceId)
    {
        $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,false);
        $this->getContext()->setError(Msg::FACE_LOGIN_ERROR);
        $this->getContext()->setStatusCode(StatusCode::FACE_LOGIN_ERROR);
        return array();
    }

    protected function doSelectByAppIdSecret($appId, $secret)
    {
        $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,false);
        $this->getContext()->setError(Msg::GET_ACCESS_TOKEN_ERROR);
        $this->getContext()->setStatusCode(StatusCode::GET_ACCESS_TOKEN_ERROR);
        return array();
    }
}