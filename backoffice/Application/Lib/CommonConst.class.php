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
    const APP_USER_ANDROID                = '10';       //android端登陆
    const APP_USER_IOS                    =  '20';       //ios端登陆
    const PC_ADMIN                         = '30';       //管理员pc端登陆
    const H5_ADMIN                         = '40';       //管理员h5端登陆
    const PC_USER                          = '50';        //用户pc端登陆
    const H5_USER                          = '60';        //用户h5端登陆

    //返回代码统一定义
    const CODE_SUCCESS               =  1;//返回结果，成功
    const CODE_ERROR                 =  0;//返回结果，失败

    //接口返回代码统一定义
    const API_CODE_SUCCESS           =  1;//接口返回结果
    const API_CODE_ERROR             =  0;//接口返回结果

    //修改权限
    const CAN_EDIT                   = 1;//可以修改
    const NO_EDIT                    = 0;//不可以修改

    //逻辑标记位
    const DEL_FLG_OK                       = 0;       //未删除
    const DEL_FLG_DELETED                  = 1;       //已删除

    //车辆分组
    const VEHICLE_GROUPS                   = 10;        //车辆分组

    //员工管理权限
    const R_ADMIN_LISTS           =  220; //员工列表

    const R_SHOW_SYSTEM           =  130;//架构列表
    const R_EXPORT_DEPARTMENT     =  113;//导出部门
    const R_EXPORT_ADMIN          =  123;//导出员工

    const R_ADD_DEPARTMENT        =  110;//添加部门
    const R_MOD_DEPARTMENT        =  111;//修改部门
    const R_DEL_DEPARTMENT        =  112;//删除部门

    const R_ADD_ADMIN             =  120;//添加员工
    const R_MOD_ADMIN             =  121;//修改员工
    const R_DEL_ADMIN             =  122;//删除员工

    //公司审核状态
    const VERIFY_STATUS_ING         = 10;//审核中
    const VERIFY_STATUS_NO          = 20;//未通过
    const VERIFY_STATUS_OK          = 30;//通过
    const VERIFY_STATUS_REFUSE      = 40;//拒绝

    //系统设置
    const SYSTEM_SET_WARNING            = 10;           //预警设置
    const SYSTEM_SET_WARNING_DIALOG    = 20;            //预警弹框设置

    //公司类型
    const ROOT_COMPANY              = 10; //父类公司
    const SUB_COMPANY               = 20; //一级子公司

}