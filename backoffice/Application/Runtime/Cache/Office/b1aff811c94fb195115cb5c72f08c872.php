<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="background-color: #f2f2f2;color: #666;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>安全云平台</title>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />
    <div class="layui-header">
        <div class="layui-logo">安全云平台</div>
        <!-- 头部区域（可配合layui已有的水平导航） -->
        <?php echo ($topMenu); ?>
        <!--头部menu end-->
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Driver/behaviorLists">
                    <i class="layui-icon layui-icon-notice"><span class="layui-badge" id="waringNum">0</span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Admin/adminMsg">
                    <i class="layui-icon layui-icon-reply-fill"><span  class="layui-badge" id="msgNum"><?php echo ($msgNum); ?></span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="javascript:;">管理员：<?php echo ($username); ?></a>
                <dl class="layui-nav-child">
                    <dd><a href="<?php echo ($baseUrl); ?>/Admin/adminInfo">个人信息</a></dd>
                    <dd><a href="<?php echo ($baseUrl); ?>/Logout">退了</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <div class="layui-side layui-bg-black">
        <div class="layui-side-scroll">

            <?php echo ($menu); ?>
        </div>
    </div>

    <div class="layui-body">
        <div style="background-color: #f5f5f5;min-height: 35px;line-height: 35px;padding: 0 12px 0;">
            <span class="layui-breadcrumb" lay-separator=">">

                <?php if(is_array($breadcrumb)): foreach($breadcrumb as $name=>$url): if(!empty($url)): ?><a href="<?php echo ($url); ?>"><?php echo ($name); ?></a>

                                <?php else: ?>
                                <a href="#"><?php echo ($name); ?></a><?php endif; endforeach; endif; ?>
            </span>
        </div>

        <!-- 内容主体区域 -->
        <div class="layui-fluid">
            
    <div class="layui-card">
        <div class="layui-card-body">
            <input type="hidden" id="adminId" value="<?php echo ($admin_id); ?>">
            <form class="layui-form layui-form-pane" action="javascript:;" method="post">

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">姓名</label>
                        <div class="layui-input-inline">
                            <input type="text" id="name" name="name" lay-verify="name" placeholder="姓名"  maxlength="4" autocomplete="off" class="layui-input" value="<?php echo ($adminInfo->name); ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">登录账号</label>
                        <div class="layui-input-inline">
                            <input type="text" id="account" name="account" lay-verify="account" placeholder="3到16位数字或字母"  maxlength="16" autocomplete="off" class="layui-input" value="<?php echo ($adminInfo->account); ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">手机号码</label>
                        <div class="layui-input-inline">
                            <input type="text" id="phone" name="phone" lay-verify="phone|required" placeholder="手机号码" maxlength="11" autocomplete="off" class="layui-input" value="<?php echo ($adminInfo->phone); ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">邮箱</label>
                        <div class="layui-input-inline">
                            <input type="text" id="email" name="email" lay-verify="email|required" placeholder="邮箱" maxlength="" autocomplete="off" class="layui-input" value="<?php echo ($adminInfo->email); ?>">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item" id="updatePass">
                    <label class="layui-form-label" style="width: 90px">登录密码</label>
                    <div class="layui-input-inline">
                        <button id="updatePassBtn" class="layui-btn layui-btn-normal">修改</button>
                    </div>
                </div>

                <div id="updatePassDiv" style="display: none">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label" style="width: 90px">原密码</label>
                            <div class="layui-input-inline">
                                <input type="password" name="oldPassword" id="oldPassword" lay-verify="oldPassword" class="layui-input" maxlength="16" placeholder="不修改，请勿填写"/>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label" style="width: 90px">新密码</label>
                            <div class="layui-input-inline">
                                <input type="password" name="newPassword" id="newPassword" lay-verify="newPassword" class="layui-input" maxlength="16" placeholder="不修改，请勿填写"/>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label" style="width: 90px">确认密码</label>
                            <div class="layui-input-inline">
                                <input type="password" name="verifyPassword" id="verifyPassword" lay-verify="verifyPassword" class="layui-input" maxlength="16" placeholder="不修改，请勿填写"/>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="layui-form-item">
                    <div class="layui-inline">
                        <button class="layui-btn" lay-submit="" lay-filter="demo1">修改</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        </div>

    </div>

    <div class="site-tree-mobile layui-hide"><i class="layui-icon"></i></div>

    <div class="layui-footer">
        <!-- 底部固定区域 -->

    </div>
</div>
<div class="warning-pop"></div>
<div class="site-mobile-shade"></div>
<script src="/Public/Js/jquery.min.js"></script>
<script src="/Public/layui/layui.js"></script>
<script src="/Public/Js/office/mqttws31.js?version=<?php echo ($version); ?>"></script>
<script src="/Public/Js/office/getNews.js?version=<?php echo ($version); ?>"></script>
<script>
    //JavaScript代码区域
    layui.use('element', function(){
        var element = layui.element;
        var treeMobile = $('.site-tree-mobile'),
                shadeMobile = $('.site-mobile-shade');

        treeMobile.on('click', function(){
            $('body').addClass('site-mobile');
        });

        shadeMobile.on('click', function(){
            $('body').removeClass('site-mobile');
        });
    });

</script>
<!-- add page js-->
<?php if(isset($js_all_files)): if(is_array($js_all_files)): foreach($js_all_files as $key=>$js_file_path): ?><script src="<?php echo ($js_file_path); ?>" type="text/javascript"></script><?php endforeach; endif; endif; ?>
<!-- end of add js-->

<!-- add page js-->
<?php if(isset($js_files)): if(is_array($js_files)): foreach($js_files as $key=>$js_file_path): ?><script src="/Public/Js/<?php echo ($js_file_path); ?>?version=<?php echo ($version); ?>" type="text/javascript"></script><?php endforeach; endif; endif; ?>



<!-- end of add js-->
</body>
</html>