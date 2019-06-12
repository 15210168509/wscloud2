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
    const FACE_LOGIN_ERROR          = "人脸识别登录失败";
    const USER_SETTING_TYPE_ERROR   = "个性化类型错误";
    const USER_BEHAVIOR_TYPE_ERROR  = "行为类型错误";
    const USER_BEHAVIOR_LEVEL_ERROR = "行为级别错误";
    const USER_BEHAVIOR_CODE_ERROR   = "行为编号错误";

    const CREATE_TOKEN_ERROR         = "创建token失败";
    const TOKEN_EXPIRED              = "token过期";
    const TOKEN_REFRESH_SUCCESS      = "token刷新成功";
    const TOKEN_REFRESH_ERROR        = "token刷新失败";
    const DEL_TOKEN_ERROR            = "删除token失败";
    const API_LIMIT_EXCEEDED         = "接口达到上限";
    const USER_NOT_EXIST              = "用户不存在";
    const CREATE_TOKEN_SUCCESS       = "创建token成功";
    const GET_ACCESS_TOKEN_SUCCESS   = "获取access_token成功";
    const GET_ACCESS_TOKEN_ERROR     = "获取access_token失败";

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
    const ADD_SUCCESS                     = "添加成功";
    const ADD_ERROR                       = "添加失败";
    const EMAIL_EXIST                     = '邮箱已存在';
    const PHONE_EMAIL_ACCOUNT            = '手机号，账号或邮箱已存在';
    const VEHICLE_EXIST                  = "车辆已存在";
    const COMPANY_PACKAGE_OK             = "套餐正常";
    const COMPANY_PACKAGE_EXPIRE         = "套餐过期";
    const OTHER_DEVICE_LOGIN             = "已在其他设备上登录";
    const SETTING_SUCCESS                = "设置成功";
    const SETTING_ERROR                  = "设置失败";
    const DEVICE_IS_BIND                 = '设备号已绑定其他车辆';
    const DEL_OK                          = '删除成功';
    const DEL_NO                          = '删除失败';
    const PACKAGE_NO_ENOUGH              = '套餐可用设备量不足';
    const DRIVER_EXIST                    = '司机已存在';
    const DEVICE_EXIST                    = '设备已存在';

    //AI返回结果
    const AI_USER_REGISTER_SUCCESS           = '用户注册成功';
    const AI_USER_EXIST                       = '用户已存在';
}