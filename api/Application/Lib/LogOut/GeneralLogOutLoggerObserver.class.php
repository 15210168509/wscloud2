<?php
/**
 * 登录观察者：记录信息日志
 * Created by dbn.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:32
 */

namespace Lib\LogOut;
use Lib\Status;
use Lib\Tools;

class GeneralLogOutLoggerObserver extends LogOutObserver
{
    public function doUpdate(LogOut $logOut)
    {
        $logOutContext = $logOut->getContext();
        // TODO: Implement doUpdate() method.
        if($logOutContext->get('')){
            //记录数据到日志

        }
    }
}