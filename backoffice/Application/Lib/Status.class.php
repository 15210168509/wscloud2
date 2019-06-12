<?php

/**
 * 常量定义
 * Created by wrf
 * Date: 2016/12/14
 * Time: 10:30
 */
namespace Lib;
class Status
{

    // 用户消息状态定义
    const USER_MSG_UNREAD       = 10; // 未读
    const USER_MSG_READ         = 20; // 已读

    // 短息模板类型定义
    const MSG_USER_SIGN_UP      = 1;  // 用户注册

    //设备激活状态
    const ACTIVE_OK                 = 20;//已激活
    const ACTIVE_NO                 = 10;//未激活

    const COMPANY_VERIFY_STATUS_ING         = 10;//审核中
    const COMPANY_VERIFY_STATUS_ON          = 20;//未通过
    const COMPANY_VERIFY_STATUS_OK          = 30;//通过
    const COMPANY_VERIFY_STATUS_REFUSE      = 40;//拒绝

}