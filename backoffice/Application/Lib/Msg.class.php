<?php
/**
 * Created by PhpStorm.
 * User: 02
 * Date: 2018/3/1
 * Time: 14:18
 */

namespace Lib;

class Msg
{
    const OK                         = "查询正常";

    const NO_DATA                    = "查询数据为空";
    const DATA_ERROR                 = "数据有误";
    const PARA_MISSING               = "参数缺失";

    const LOGIN_SUCCESS              = "登录成功";
    const LOGIN_ERROR                = "用户名或密码错误";
    const LOGIN_TYPE_ERROR           = "用户类型错误";
    const LOGOUT_SUCCESS             = "退出成功";
    const LOGOUT_ERROR                = "退出失败";
    const REGISTER_SUCCESS           = "注册成功";
    const REGISTER_ERROR             = "注册失败";
    const ACCOUNT_EXIST              = "用户名已存在";
    const GET_USER_INFO_ERROR       = "获取用户信息失败";

    const CREATE_TOKEN_ERROR         = "创建token失败";
    const TOKEN_EXPIRED              = "token过期";
    const TOKEN_REFRESH_SUCCESS      = "token刷新成功";
    const TOKEN_REFRESH_ERROR        = "token刷新失败";
    const DEL_TOKEN_ERROR            = "删除token失败";
    const API_LIMIT_EXCEEDED         = "接口达到上限";
    const USER_NOT_EXIST              = "用户不存在";
    const CREATE_TOKEN_SUCCESS       = "创建token成功";

    const PHONE_EXIST                 = "手机号码已存在";
    const PHONE_NOT_EXIST             = "手机号码不存在";
    const VERIFICATION_CODE_RIGHT    = "验证码正确";
    const VERIFICATION_CODE_ERROR    = "验证码错误";
    const PHONE_REQUIRED              = "手机号码不能为空";
    const PHONE_INVALID               = "手机号码不正确";
    const VERIFY_CODE_REQUIRED       = "验证码不能为空";
    const SMS_TEMPLET_ERROR           = "短信模板错误";
    const SEND_VERIFY_CODE_SUCCESS   = "已发送验证码";
    const CODE_FAIL_SAVE              = "验证码保存失败";
    const CODE_FAIL_SEND              = "发送验证码失败";
    const UPDATE_ERROR                 = "更新失败";
    const UPDATE_SUCCESS               = "更新成功";
    const VERIFY_OLD_PASSWORD_ERROR   = "旧密码不一致";
    const PASSWORD_LENGTH_ERROR        = "密码长度不符合";
    const SET_ERROR                      = "设置失败";
    const SET_SUCCESS                    = "设置成功";
    const UPLOAD_SUCCESS                 = "上传成功";
    const UPLOAD_ERROR                   = "上传失败";
    const GET_DATA_SUCCESS               = "查询成功";
    const GET_DATA_ERROR                 = "查询失败";
    const OTHER_DEVICE_LOGIN             = "已在其他设备上登录";
    const ADD_SUCCESS                   = '添加成功';
    const ADD_ERROR                     = '添加失败';
    const EMAIL_EXIST                     = '邮箱已注册';

    const HTTP_ERROR                 = "访问服务器失败";

    const MSG_ACCOUNT_UNREGISTERED  =  '登录账户未注册';
    const MSG_ACCOUNT_REGISTERED    =  '登录账户已注册';
    const MSG_PHONE_UNREGISTERED    =  '手机号码未注册';
    const MSG_PHONE_REGISTERED      =  '手机号码已注册';
    const MSG_PHONE_CHECK_ERROR     =  '手机号码非法';
    const MSG_PASS_TWO_ERROR        =  '两次输入密码不一致';


    const MSG_DEL_SUCCESS               =   '删除成功';
    const MSG_DEL_ERROR                 =  '删除失败';
    const PHONE_EMAIL_ACCOUNT             = '手机号，账号或邮箱已存在';

    const  DEVICE_ACTIVE_OK             = '设备激活成功';
    const  DEVICE_ACTIVE_NO             = '设备激活失败';
    const   DEVICE_NO_EXIST               = '设备不存在';


}