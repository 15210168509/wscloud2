<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/4/17
 * Time: 15:35
 */

namespace Lib;


class StatusCode
{
    const OK                                 = 1000;             //查询成功，有预期数据返回
    const NO_DATA                           = 1001;             //查询成功，预期数据为空

    const CREATE_TOKEN_ERROR              = 2000;              //创建token失败
    const TOKEN_EXPIRED                    = 2001;             //token已过期（不存在）
    const TOKEN_REFRESH_SUCCESS           = 2002;             //token刷新成功
    const TOKEN_REFRESH_ERROR             = 2003;             //token刷新失败
    const DEL_TOKEN_ERROR                 = 2004;             //删除token失败
    const API_LIMIT_EXCEEDED              = 2005;             //接口调用达到上限
    const USER_NOT_EXIST                   = 2006;            //用户不存在
    const CREATE_TOKEN_SUCCESS            = 2007;             //创建token成功

    const LOGIN_SUCCESS                    = 3000;             //登录成功
    const LOGIN_ERROR                      = 3001;             //登录失败，用户名或密码错误
    const LOGIN_TYPE_ERROR                = 3002;             //用户登录类型错误
    const LOGOUT_SUCCESS                   = 3003;            //退出成功
    const LOGOUT_ERROR                     = 3004;            //退出失败
    const REGISTER_SUCCESS                 = 3005;            //注册成功
    const REGISTER_ERROR                   = 3006;            //注册失败
    const ACCOUNT_EXIST                    = 3007;            //用户名已存在
    const GET_USER_INFO_ERROR             = 3008;             //获取用户信息失败
    const FACE_LOGIN_ERROR                 = 3009;            //faceId不存在
    const USER_SETTING_TYPE_ERROR         = 3010;            //个性化类型错误
    const USER_BEHAVIOR_TYPE_ERROR        = 3011;            //行为类型错误
    const USER_BEHAVIOR_LEVEL_ERROR       = 3012;            //行为级别错误
    const USER_BEHAVIOR_CODE_ERROR        = 3013;            //行为编号错误
    const GET_ACCESS_TOKEN_SUCCESS        = 3014;            //获取access_token成功
    const GET_ACCESS_TOKEN_ERROR           = 3015;            //获取access_token失败
    const EMAIL_LOGIN_ERROR                 = 3016;            //邮箱或密码错误

    const PARA_MISSING                      = 4001;             //参数缺失
    const PHONE_EXIST                       = 4005;            //手机号码存在
    const PHONE_NOT_EXIST                   = 4006;            //手机号码不存在
    const VERIFICATION_CODE_RIGHT          = 4007;            //验证码正确
    const VERIFICATION_CODE_ERROR          = 4008;            //验证码错误
    const PHONE_REQUIRED                    = 4009;            //手机号码不能为空
    const PHONE_INVALID                     = 4010;             //手机号码不正确
    const VERIFY_CODE_REQUIRED             = 4011;             //验证码不能为空
    const SMS_TEMPLET_ERROR                 = 4012;             //短信模板错误
    const DATA_ERROR                         = 4013;             //数据有误
    const SEND_VERIFY_CODE_SUCCESS          = 4014;             //发送验证码成功
    const CODE_FAIL_SEND                     = 4015;             //发送验证码失败
    const CODE_FAIL_SAVE                     = 4016;             //验证码保存失败
    const UPDATE_SUCCESS                     = 4017;             //更新成功
    const UPDATE_ERROR                       = 4018;              //更新失败
    const VERIFY_OLD_PASSWORD_ERROR         = 4019;              //旧密码不一致
    const PASSWORD_LENGTH_ERROR             = 4020;              //密码长度不符合
    const SET_SUCCESS                        = 4021;              //设置成功
    const SET_ERROR                           = 4022;              //设置失败
    const UPLOAD_SUCCESS                      = 4023;             //上传成功;
    const UPLOAD_ERROR                        = 4024;             //上传失败;
    const GET_DATA_SUCCESS                   = 4025;              //查询成功
    const GET_DATA_ERROR                      = 4026;              //查询失败
    const ADD_SUCCESS                         = 4027;               //添加成功
    const ADD_ERROR                           = 4028;               //添加失败
    const OTHER_DEVICE_LOGIN                  = 4029;              //其他设备登录
    const EMAIL_EXIST                         = 4030;              //邮箱已注册
    const PHONE_EMAIL_ACCOUNT                 = 4031;               //手机号，账号或邮箱已存在
    const VEHICLE_EXIST                       = 4032;               //车辆已存在
    const COMPANY_PACKAGE_OK                 = 4033;                 //套餐正常
    const COMPANY_PACKAGE_EXPIRE             = 4034;                 //套餐过期
    const SETTING_SUCCESS                     = 4035;                 //设置成功
    const SETTING_ERROR                       = 4036;                 //设置失败
    const DEVICE_IS_BIND                       = 4037;                  //设备号已绑定其他车辆
    const DEL_OK                               = 4038;                  //删除成功
    const DEL_NO                               = 4039;                  //删除失败
    const PACKAGE_NO_ENOUGH                   = 4040;                 //套餐可用设备量不足';
    const DRIVER_EXIST                         = 4041;                //司机已存在
    const DEVICE_EXIST                         = 4042;                  //设备已存在

    //AI返回结果
    const AI_USER_REGISTER_SUCCESS           = 4048;                  //用户注册成功
    const AI_USER_EXIST                        = 4050;                  //用户已存在
}