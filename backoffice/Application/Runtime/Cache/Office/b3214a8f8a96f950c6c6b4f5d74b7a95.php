<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="height: 100%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>微视云监控平台</title>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body">

    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />
    <div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <div class="layui-logo">微视云监控平台</div>

        <ul class="layui-nav layui-layout-right">

            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Admin/adminMsg">
                    <i class="layui-icon layui-icon-reply-fill"><span  class="layui-badge" id="msgNum"><?php echo ($msgNum); ?></span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="javascript:;">
                    <img src="http://t.cn/RCzsdCq" class="layui-nav-img">
                    <?php echo ($username); ?>
                </a>
                <dl class="layui-nav-child">
                    <dd><a href="<?php echo ($baseUrl); ?>/Logout">退了</a></dd>
                </dl>
            </li>
        </ul>
    </div>

        <div style="height: 100%">
            
    <div class="layui-card">
        <div class="layui-card-body">
            <input type="hidden" id="adminId" value="<?php echo ($admin_id); ?>">
            <div style="margin: 10px">您公司当前的审核状态为：<?php echo ($company['verify_status_str']); ?></div>
            <form class="layui-form layui-form-pane" action="javascript:;" method="post" style="text-align: center">

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">公司名称</label>
                        <div class="layui-input-inline">
                            <input type="text" id="name" name="name" lay-verify="name" placeholder="公司名称"  value="<?php echo ($company['name']); ?>" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                </div>


                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">手机号码</label>
                        <div class="layui-input-inline">
                            <input type="text" id="phone" name="phone" lay-verify="phone|required" placeholder="手机号码" value="<?php echo ($company['phone']); ?>" maxlength="11" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label" style="width: 90px">邮箱</label>
                        <div class="layui-input-inline">
                            <input type="text" id="email" name="email" lay-verify="email|required" placeholder="邮箱" value="<?php echo ($company['email']); ?>" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                </div>
                <?php if($company["verify_status"] == 20): ?><div class="layui-form-item hidebtn">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit="" lay-filter="demo1">修改</button>
                            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                        </div>
                    </div><?php endif; ?>

            </form>


        </div>
    </div>

        </div>


</div>


<script src="/Public/Js/jquery.min.js"></script>
<script src="/Public/layui/layui.js"></script>
<script src="/Public/Js/office/mqttws31.js?version=<?php echo ($version); ?>"></script>
<script src="/Public/Js/office/getNews.js?version=<?php echo ($version); ?>"></script>
<script>
    //JavaScript代码区域
    layui.use('element', function(){
        var element = layui.element;

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