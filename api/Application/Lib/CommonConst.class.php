<?php

/**
 * 常量定义
 * Created by wrf
 * Date: 2017/2/28
 * Time: 10:30
 */
namespace Lib;

class CommonConst
{
    //应用程序类型，日志类型
    const PC_ADMIN                         = '10';       //管理员pc端登陆
    const H5_ADMIN                         = '20';       //管理员h5端登陆
    const PC_MANAGER                       = '30';         //后台管理员pc端登陆
    const THIRD_COMPANY_APP_ID_SECRET     = '80';        //第三方公司，app_id,secret获取access_token


    //责任管理员
    const ADMIN_TYPE_NORMAL                 = 10;         //普通管理员
    const ADMIN_TYPE_ADMIN                  = 20;         //责任管理员


    //登录方式
    const  LOGIN_BY_ACCOUNT               = 10;          //用户名密码登录

    //用户替他设别登陆
    const OTHER_DEVICE_LOGIN             = -1;          //用户在其他设备上登录

    //逻辑标记位
    const DEL_FLG_OK                       = 0;       //未删除
    const DEL_FLG_DELETED                  = 1;       //已删除

    //密码长度
    const PASSWORD_MIN_LENGTH             = 6;        //密码最小长度
    const PASSWORD_MAX_LENGTH             = 16;       //密码最小长度

    //消息类型
    const MSG_TYPE_SYSTEM                 = 10;         //系统消息
    const MSG_TYPE_TIRED                  = 20;         //疲劳消息


    //短信常量定义
    const PHONE_SEND_MAX                   = 20;      //最多发送短信数量
    const USER_REGISTER_SMS                = 1;       //用户注册短信模板
    const REGISTER_SUCCESS_SMS             = 2;         //注册成功通知
    const RESET_ADMIN_PWD                  = 3;         //重置密码

    const TOPIC_ADMIN                       = 'office';  //管理员消息主题
    const TOPIC_MANAGER                     = 'manager';  //管理员消息主题

    const VEHICLE_GROUPS                   = 10;        //车辆分组

    public static function getGroupsType(){
        return array(self::VEHICLE_GROUPS=>'车辆分组');       //车辆分组
    }

    //公司审核状态
    const VERIFY_STATUS_ING         = 10;//审核中
    const VERIFY_STATUS_NO          = 20;//未通过
    const VERIFY_STATUS_OK          = 30;//通过
    const VERIFY_STATUS_REFUSE      = 40;//拒绝

    public static function VerifyStatus($code)
    {
        $statusTrans = array(
            'tr_10' => '审核中',
            'tr_20' => '审核未通过',
            'tr_30' => '审核通过',
            'tr_40' => '审核拒绝',
        );
        return $statusTrans['tr_' . $code];
    }


    //系统设置
    const SYSTEM_SET_WARNING            = 10;           //预警设置
    const SYSTEM_SET_WARNING_DIALOG     = 20;            //预警弹框设置

    const UPLOAD_DRIVER_FACE_OK         = 1;            //上传人脸照片成功
    const UPLOAD_DRIVER_FACE_ERROR      = 0;            //上传人脸照片失败

    //设备类型定义
    const DEVICE_RUICHENG               = 10;  //锐承设备
    const DEVICE_DIPINGXIAN             = 30;  //地平线设备

    //地图类型定义
    const BD_MAP                        = 10;//百度地图
    const GD_MAP                        = 20;//高德地图

}