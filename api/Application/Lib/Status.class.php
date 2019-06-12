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

    //管理员状态定义
    const ADMIN_OK                  = 10;                    //正常
    const ADMIN_LOCKED              = 20;                    //锁定

    const MANAGER_OK                = 10;                     //后台管理员，正常
    const MANAGER_LOCKED           = 20;                    //后台管理员，锁定


    // 用户消息状态定义
    const USER_MSG_UNREAD       = 10; // 未读
    const USER_MSG_READ         = 20; // 已读



    //设备激活状态
    const ACTIVE_OK                 = 20;//已激活
    const ACTIVE_NO                 = 10;//未激活

    //用户状态
    public static function AdminStatus2Str($code)
    {
        $statusTrans = array(
            'tr_10' => '正常',
            'tr_20' => '锁定',
        );
        return $statusTrans['tr_' . $code];
    }

    // 用户消息状态
    public static function MsgStatus2Str($code)
    {
        $statusTrans = array(
            'tr_10' => '未读',
            'tr_20' => '已读',
        );
        return $statusTrans['tr_' . $code];
    }

    //消息类型
    public static function msgType2Str($type)
    {
        $statusTrans = array(
            'ty_10'=>'系统消息',
            'ty_20'=>'其他',
        );
        return $statusTrans['ty_'.$type];
    }
    //设备所属
    public static function belong2Str($type)
    {
        $statusTrans = array(
            'be_10'=>'企业',
            'be_20'=>'个人',
        );
        return $statusTrans['be_'.$type];
    }
    //设备类型
    public static function deviceType2Str($type)
    {
        $statusTrans = array(
            'ty_10'=>'微视摄像头',
            'ty_30'=>'ADAS+DMS设备'
        );
        return $statusTrans['ty_'.$type];
    }
    //设备激活状态
    public static function active2Str($type)
    {
        $statusTrans = array(
            'ty_10'=>'<span class="red">未激活</span>',
            'ty_20'=>'已激活'
        );
        return $statusTrans['ty_'.$type];
    }
    //公司审核状态
    public static function companyVerifyStatus2Str($status)
    {
        $statusTrans = array(
            'ty_10'=>'审核中',
            'ty_20'=>'未通过',
            'ty_30'=>'通过',
            'ty_40'=>'拒绝',
        );
        return $statusTrans['ty_'.$status];
    }

    const  COMPANY_EXPIRE_STATUS_OK = 10;//有效
    const  COMPANY_EXPIRE_STATUS_NO = 20;//过期

    //设备在线状态
    const DEVICE_ON_LINE = 10;//设备在线
    const DEVICE_OFF_LINE = 20;//设备离线

    const GET_DEVICE_SETTING    = 10;//获取配置
    const PUSH_DEVICE_SETTING   = 20;//推送配置
    const DEVICE_RESTART        = 30;//设备重启
    const CHECK_VEHICLE_PICTURE = 40;//获取车辆设备的安装情况的图片
    const DEVICE_UPDATE         = 50;//设备升级
    const CMD                   = 80;
}