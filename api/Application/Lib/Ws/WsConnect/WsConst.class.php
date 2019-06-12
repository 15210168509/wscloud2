<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/15
 * Time: 11:37
 */

namespace Lib\Ws\WsConnect;


class WsConst
{
    const CODE_ERROR        = 0;           //返回结果，失败
    const CODE_OK           = 1;         //返回结果，成功

    const STATUS_TOKEN_EXPIRED              = 2001;             //token已过期（不存在）
    const STATUS_TOKEN_REFRESH_ERROR        = 2003;             //token刷新失败
    const STATUS_OTHER_DEVICE_LOGIN         = 4027;              //其他设备登录
    const STATUS_HTTP_ERROR                  = 5000;              //访问服务器失败


    const MSG_TOKEN_EXPIRED                  = 'token过期';
    const MSG_TOKEN_REFRESH_ERROR           = 'token刷新失败';
    const MSG_OTHER_DEVICE_LOGIN             = '在其他设备上登录';
    const MSG_HTTP_ERROR                      = '访问服务器失败';
}