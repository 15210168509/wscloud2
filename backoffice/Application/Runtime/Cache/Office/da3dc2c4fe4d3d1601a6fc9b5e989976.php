<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title>微视云监控平台</title>

    <meta name="description" content="User login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="icon" href="/Public/Images/office/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <link rel="stylesheet" href="/Public/CSS/office/login/animations.css"/>
    <link rel="stylesheet" href="/Public/CSS/office/login/login.css">

</head>

<body class="layui-layout-body login-layout">
<div class="layui-layout layui-layout-admin">


    <div class="layui-header" style="background-color: rgba(35,38,46,0.5);">
        <div class="layui-logo" style="width: 190px;"><img style="width: 24%;margin: 5px;" src="/Public/Images/office/web-logo.png">微视云监控平台</div>


        <div class="navbar navbar-default" style="background-color:#2b3d53">

        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="http://open.56xun.cn">开放平台</a>
            </li>
            <li class="layui-nav-item">
                <a href="http://www.hx-microview.com"> 官网地址 </a>
            </li>
        </ul>
    </div>
    </div>

    <div class="layui-row">

        <div class="layui-row layui-col-xs12" style="margin: 3% 0;position: relative;z-index: 2;">
            <h4 class="blue" id="id-company-text"></h4>
        </div>

        <div class="layui-col-md7 layui-col-xs12">
            <div class="layui-col-md6 layui-col-xs12 layui-col-md-offset5" style="color: #fff;padding: 10% 5%">
                <h1>微视云监控平台</h1>
                融合AI突破性技术，解决社会和商业棘手问题<br/>
                提供ET大脑帮助您在复杂局面下，做出最优决策<br/>
                现已广泛应用于交通、运输、物流、安全驾驶等多个行业</br>
            </div>
        </div>

        <div class="layui-row layui-col-md5 layui-col-xs12 pt-perspective" id="widget-box" style="padding: 1% 5%">
            <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
            <div class="layui-card layui-row login-box pt-page pt-page-1" style="background-color: transparent; " id="login-box">
                <div class="layui-col-md7 layui-col-xs12" style="background-color: rgba(255,255,255,0.5);">
                    <div style="background-color: #F7F7F7;  margin: 6px">
                        <div class="layui-card-header"> <h3><i class="layui-icon layui-icon-user" style="font-size: 16px"></i>&nbsp;公司登录</h3></div>
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
                                    <div class="layui-trans layui-form-item layadmin-user-login-other">
                                        <a href="#" id="registerLink" data-target="#signup-box" class="layadmin-user-jump-change layadmin-link user-signup-link">注册帐号</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="layui-card layui-row signup-box no-border pt-page pt-page-2" style="background-color: transparent;" id="signup-box">

                <div class="layui-col-md7 layui-col-xs12" style="background-color: rgba(255,255,255,0.5);">
                    <div style="background-color: #F7F7F7;  margin: 6px">

                        <div class="layui-card-header"> <h3><i class="layui-icon layui-icon-user" style="font-size: 16px"></i>&nbsp;公司注册</h3></div>
                        <div class="layui-card-body">
                            <div class="layadmin-user-login-main">
                                <div class="layadmin-user-login-box layadmin-user-login-body layui-form">

                                    <form id="create_form">

                                        <div class="layui-form-item">
                                            <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="registerCompanyName"></label>
                                            <input type="text" name="registerCompanyName" id="registerCompanyName" lay-verify="nickname|required" placeholder="公司名称" class="layui-input" maxlength="20">
                                        </div>

                                        <div class="layui-form-item">
                                            <label class="layadmin-user-login-icon layui-icon layui-icon-layer" for="registerCompanyName"></label>
                                            <input type="text" name="registerCompanyContactEmail" id="registerCompanyContactEmail"  placeholder="公司邮箱(选填)" class="layui-input" maxlength="20">
                                        </div>

                                        <div class="layui-form-item">
                                            <label class="layadmin-user-login-icon layui-icon layui-icon-cellphone" for="registerCompanyContactPhone"></label>
                                            <input type="text" name="registerCompanyContactPhone" id="registerCompanyContactPhone" lay-verify="phone" placeholder="手机" class="layui-input" maxlength="11">
                                        </div>
                                        <div class="layui-form-item">
                                            <div class="layui-row">
                                                <div class="layui-col-xs7">
                                                    <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="registerAuthCode"></label>
                                                    <input type="text" name="registerAuthCode" id="registerAuthCode" lay-verify="required" placeholder="验证码" class="layui-input" maxlength="4">
                                                </div>
                                                <div class="layui-col-xs5">
                                                    <div style="margin-left: 10px;">
                                                        <button type="button" class="layui-btn layui-btn-primary layui-btn-fluid" id="btnRegisterAuthCode">获取验证码</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="layui-form-item">
                                            <div style="float: left; color: rgb(224, 90, 90);height:10px; margin-top:-3px;" id="registerTest"></div>
                                        </div>
                                        <div class="layui-form-item">
                                            <button class="layui-btn layui-btn-fluid" lay-submit="" id="create_bt" lay-filter="LAY-user-reg-submit">注 册</button>
                                        </div>

                                        <div class="layui-form-item" id="registerSuccessBox" style="display: none;">
                                            <div class="registerSuccessIcon">
                                                <i class="ace-icon fa fa-check"></i>
                                            </div>
                                            <span class="registerSuccessInfo">
                                            注册成功，信息正在审核中，请耐心等待审核结果!
                                        </span>
                                        </div>

                                        <div class="layui-trans layui-form-item layadmin-user-login-other">
                                            <a href="#" data-target="#login-box" class="layadmin-user-jump-change layadmin-link user-signup-link">用已有帐号登入</a>
                                        </div>
                                    </form>
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
<script src="/Public/Js/office/loginRegister/transition.js"></script>
<script>
    jQuery(function($) {
        var translate = new transition({
            $main: $('#widget-box'),
            loop: true,
        });

        $(document).on('click', 'a[data-target]', function (e) {
            translate.nextPage(33);
        })
    });
</script>
<script src="/Public/Js/office/loginRegister/register.js"></script>
<script src="/Public/Js/office/loginRegister/login.js"></script>
</body>
</html>