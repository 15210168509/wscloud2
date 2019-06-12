<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="height: 100%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>安全云平台</title>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body" style="height: 100%">

    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />


        <div style="height: 100%">
            
    <!--排版一-->

    <div class="layui-col-md3" style="height: 100%;background-color: #0e2147">
        <div id="tired_no" class="dataBox"></div>
        <div id="tired_type" class="dataBox"></div>
    </div>

    <div class="layui-col-md6" style="height: 100%;background-color: #0e2147">
        <div class="layui-col-md12" style="background-color: #0e2147;color: #fff;font-size: 16px;text-align: center;height: 10%;">
            实时数据监控
            <!--<button style="background-color:#516b91;margin-left: 20px" class="layui-btn  layui-btn-sm" id="vehicleListBtn">车辆选择</button>-->
            <div id="nowDiv" style="font-size: 36px;color: #e2e2e2;"></div>
        </div>

        <div class="layui-col-md12" id="map" style="height: 88%;background-color: #0e2147"></div>
    </div>

    <div class="layui-col-md3" style="height: 100%;background-color: #0e2147;">
        <div class="layui-col-md12 dataBox" style="height: 20%;overflow:scroll;overflow-x:hidden;">
            <div class="layui-card" style="height: 100%;background-color: #0e2147;color: #fff">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">选择车辆</div>
                <div class="layui-card-body">
                    <ul id="demo"></ul>
                </div>
            </div>
        </div>
        <div id="tired_value" class="layui-col-md12 dataBox" style="height: 26%" ></div>

        <div class="layui-col-md12 dataBox">
            <div class="layui-card" style="height: 100%;background-color: #0e2147;color: #fff">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">报警信息</div>
                <div class="layui-card-body">
                    <div class="layui-col-md4">司机</div>
                    <div class="layui-col-md4">时间</div>
                    <div class="layui-col-md4">报警类型</div>
                    <div class="layui-col-md12" id="msg"></div>
                </div>
            </div>
        </div>
    </div>



    <!--<div id="vehicleList"><ul id="demo"></ul></div>-->


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