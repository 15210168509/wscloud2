<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>微视云后台管理</title>

    <meta name="description" content="User login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="/Public/Images/office/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <link rel="stylesheet" href="/Public/CSS/office/login/animations.css"/>
    <link rel="stylesheet" href="/Public/CSS/admin/login/login.css">

</head>

<body class="layui-layout-body login-layout">
<div class="layui-layout layui-layout-admin">


    <div class="layui-header" style="background-color: rgba(35,38,46,0.5);">
        <div class="layui-logo" style="width: 190px;"><img style="width: 24%;margin: 5px;" src="/Public/Images/office/web-logo.png">微视云后台管理</div>
    </div>

    <div class="layui-row">



        <div class="layui-col-md12 layui-col-xs12" style="width: 40%;position: fixed;left: 40%;top: 15%">
            <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
            <div class="layui-card layui-row login-box" style="background-color: transparent; " >
                <div class="layui-col-md7 layui-col-xs12" style="background-color: rgba(255,255,255,0.5);">
                    <div style="background-color: #F7F7F7;  margin: 6px">
                        <div class="layui-card-header"> <h3><i class="layui-icon layui-icon-user" style="font-size: 16px"></i>&nbsp;管理员登录</h3></div>
                        <div class="layui-card-body">
                            <div class="layadmin-user-login-main">
                                <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
                                    <div class="layui-form-item">
                                        <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="login_account"></label>
                                        <input type="text" placeholder="帐号登录" name="login_account" id="login_account"  lay-verify="required" class="layui-input" maxlength="20">
                                    </div>
                                    <div class="layui-form-item">
                                        <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="login_pass"></label>
                                        <input type="password" placeholder="登录密码" name="login_pass" lay-verify="required" class="layui-input" id="login_pass" maxlength="11" >
                                    </div>
                                    <div class="layui-form-item">
                                        <span id="login_test" style="float: left; color: rgb(224, 90, 90);height:10px; margin-top:-3px;"></span>
                                    </div>
                                    <div class="layui-form-item">
                                        <button class="layui-btn layui-btn-fluid" id="login_submit" lay-submit="" lay-filter="LAY-user-login-submit"><span id="login_font">登 入</span></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="footer-info" style="position: fixed;bottom:0px; padding: 1%; text-align:center;width:100%;z-index: 1;">
    <span style="color: #fff">©2018 华迅金安 京ICP备17013584号</span>
</div>
<script src="/Public/Js/jquery.min.js"></script>
<script src="/Public/Js/validate/jquery.validate.js"></script>
<script src="/Public/Js/admin/login/login.js"></script>
</body>
</html>