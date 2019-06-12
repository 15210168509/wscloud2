<?php
/**
 * 登录观察者：记录安全记录
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:32
 */
namespace Lib\Login;


class SecurityMonitorObserver extends LoginObserver
{
    public function doUpdate(AbstractLogin $login)
    {
        //用户登录结果
        if($this->longContext->get(AbstractLogin::LOGIN_LABEL)){
            //用户登录成功，记录登录数据到日志
        } else {
            //用户登录失败，记录警报日志
        }
    }
}