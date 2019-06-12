<?php
/**
 * 登录观察者：清除用户登录信息
 * Created by dbn.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:32
 */

namespace Lib\LogOut;
use Lib\Status;
use Lib\Tools;

class ClearLoginInfoObserver extends LogOutObserver
{
    public function doUpdate(LogOut $logOut)
    {

        // 清除用户登录信息
        if($this->longContext->get(LogOut::LOGOUT_LABEL)){

            $userInfo = $this->longContext->get(LogOut::LOGOUT_INFO);

        }

    }
}