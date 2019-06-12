<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="background-color: #f2f2f2;color: #666;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>微视云监控平台</title>
    <link rel="icon" href="/Public/Images/office/logo.png" type="image/x-icon"/>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body">
<div class="layui-layout layui-layout-admin">
    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" id="warningDialog" value="<?php echo ($warningDialog); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />
    <div class="layui-header">
    <div class="layui-logo" style="width: 190px;"><img style="width: 24%;margin: 5px;" src="/Public/Images/office/web-logo.png">微视云监控平台</div>

        <?php echo ($topMenu); ?>
        <!--头部menu end-->
        <ul class="layui-nav layui-layout-right">

            <li class="layui-nav-item">
                <a href="<?php echo ($baseUrl); ?>/Admin/adminMsg">
                    <i class="layui-icon layui-icon-reply-fill"><span  class="layui-badge" id="msgNum"><?php echo ($msgNum); ?></span></i>
                </a>

            </li>

            <li class="layui-nav-item">
                <a href="javascript:;">管理员：<?php echo ($username); ?></a>
                <dl class="layui-nav-child">
                    <dd><a href="<?php echo ($baseUrl); ?>/Admin/adminInfo">个人信息</a></dd>
                    <dd><a href="<?php echo ($baseUrl); ?>/Logout/index">退出</a></dd>
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
        <div style="background-color: #f5f5f5;min-height: 40px;line-height: 40px;padding: 0 12px 0;">
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
            <div class="searchContainer">
                <form class="form-horizontal" id="src_form">
                    <div class="searchTop">
                        <div class="layui-col-xs6 layui-col-sm6 layui-col-md6">
                            <span class="searchTitle padding-lr20">快速检索</span>
                        </div>
                        <div class="layui-col-xs6 layui-col-sm6 layui-col-md6" style="text-align: right">
                            <button id="src_bt" class="layui-btn" >搜 索</button>
                            <button type="reset" id="reset_bt" class="layui-btn" >重 置</button>
                        </div>
                    </div>
                    <div class="layui-form layui-form-pane" style=" padding: 5px;">
                        <div class="layui-col-xs6 layui-col-sm6 layui-col-md4">
                            <label class="layui-form-label">车牌号</label>
                            <div class="layui-input-inline">
                                <input type="text" name="username" id="vehicle_no" placeholder="车牌号" class="layui-input" value="<?php echo ($where_vehicle_no); ?>">
                            </div>
                        </div>

                        <div class="layui-col-xs6 layui-col-sm6 layui-col-md4">
                            <label class="layui-form-label">公司名称</label>
                            <input type="hidden" id="companyId" value="<?php echo ($where_companyId); ?>">
                            <div class="layui-input-inline">
                                <input type="text" id="company" name="company" placeholder="公司名称"  autocomplete="on" class="layui-input" value="<?php echo ($where_company); ?>">
                            </div>
                        </div>

                        <div style="clear: both"></div>
                    </div>

                    <div class="clearfix"></div>
                </form>
            </div>
            <table class="layui-hide" id="tableList" lay-filter="tableList"></table>
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
<script src="/Public/Js/admin/mqttws31.js?version=<?php echo ($version); ?>"></script>
<script src="/Public/Js/admin/getNews.js?version=<?php echo ($version); ?>"></script>
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