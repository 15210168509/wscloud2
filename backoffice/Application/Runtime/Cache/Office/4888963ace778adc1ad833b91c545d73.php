<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html style="height: 100%">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>微视云监控平台</title>
    <link rel="stylesheet" href="/Public/layui/css/layui.css">
    <link rel="stylesheet" href="/Public/CSS/global.css">
    <link rel="icon" href="/Public/Images/office/logo.png" type="image/x-icon"/>
    <!--<link rel="stylesheet" href="/Public/CSS/base.css">-->

    <?php if(isset($css_files)): if(is_array($css_files)): foreach($css_files as $css_file_path=>$media): ?><link href='/Public/CSS/<?php echo ($css_file_path); ?>?version=<?php echo ($version); ?>' rel="stylesheet" type="text/css" /><?php endforeach; endif; endif; ?>

</head>
<body class="layui-layout-body" style="height: 100%">

    <input type="hidden" id="baseUrl" value="<?php echo ($baseUrl); ?>">
    <input type="hidden" value="<?php echo ($adminTopic); ?>" id="adminTopic" />
    <input type="hidden" value="<?php echo ($mqttServer); ?>" id="mqttServer" />
    <input type="hidden" value="<?php echo ($mqttServerPort); ?>" id="mqttServerPort" />
    <input type="hidden" value="<?php echo ($tiredWarningNumber); ?>" id="tiredWarningNumber" />

        <div style="height: 100%">
            
    <!--排版一-->
<div class="layui-col-md12 bgimg" style="height: 100%;">
    <div class="layui-col-md3 height_bgcolor">
        <!--<div id="tired_no" class="dataBox"></div>
        <div id="tired_type" class="dataBox"></div>-->
        <div  style="height: 4%"></div>
        <div class="dataBox">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="layui-card my_card" style="height: 100%">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">预警次数</div>
                <div class="layui-card-body" style="height: 85%">
                    <div id="tired_no" style="height: 100%"></div>
                </div>
            </div>

        </div>
        <div style="height: 4%"></div>
        <div class="dataBox">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="layui-card my_card">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">行为类型</div>
                <div class="layui-card-body" style="height: 85%;">
                    <div id="tired_type" style="height: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-col-md6 height_bgcolor">
        <div class="layui-col-md12" style="color: #f1f1f1;text-align: center;height: 170px;">
            <div class="layui-col-md12" style="margin-top: 10px;">
                <img style="height: 68px;" src="/Public/Images/office/title.png">
            </div>
            <div class="layui-col-md12" id="nowDiv" style="color: #e2e2e2"><?php echo date('Y年m月d日 H:i:s');?></div>
            <input id="sumTiredNo" value="<?php echo ($sumTiredNo); ?>" type="hidden">
            <div class="layui-col-md12" id="all">
                <span class="t_num t_num1"><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i><i style="background-position: 0px 0px;"></i></span>
            </div>
        </div>
        <div id="title"  class="layui-col-md12" style="border: 1px solid rgba(171,171,171,0.5);">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="vehicleMsg">
                <img id="closeVehicleMsg" class="closeMsg" src="/Public/CSS/office/driver/close.png"/>
                <div id="msgBody">

                </div>
            </div>
            <div class="layui-col-md12" id="map" style="height: 100%;"></div>
            <div id="mapStoreClass" style="position: absolute;z-index: 9999;"></div>
        </div>

    </div>

    <div class="layui-col-md3 height_bgcolor" style="padding-right: 2%">
        <div class="layui-col-md12" style="height: 4%"></div>
        <div class="layui-col-md12 dataBox bgcolor" style="height: 20%;">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="layui-card my_card" style="overflow: hidden">
                <div class="layui-card-header" style="color: #e2e2e2;border-bottom: 1px solid #999;">选择车辆</div>
                <div class="layui-card-body" style="height: 64%;overflow:auto;overflow-x:hidden;">
                    <input type="checkbox" value="1" id="allChecked">全选
                    <ul id="demo"></ul>
                </div>
            </div>
        </div>

        <div class="layui-col-md12" style="height: 4%"></div>
        <div class="bgcolor dataBox layui-col-md12" style="height: 38%">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="layui-card my_card" style="height: 100%">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">疲劳值</div>
                <div class="layui-card-body" style="height: 80%;">
                    <div id="tired_value" style="height: 100%" ></div>
                </div>
            </div>
        </div>
        <!--<div id="tired_value" class="layui-col-md12 dataBox" style="height: 46%" ></div>-->

        <div class="layui-col-md12" style="height: 4%"></div>
        <div class="layui-col-md12 dataBox bgcolor" style="height: 28%">
            <div class="corner1"></div>
            <div class="corner2"></div>
            <div class="corner3"></div>
            <div class="corner4"></div>
            <div class="layui-card my_card" style="overflow: hidden">
                <div class="layui-card-header" style="color: #fff;border-bottom: 1px solid #999;">预警信息</div>
                <div class="layui-card-body my-card-body">
                    <div class="layui-col-md3">车辆</div>
                    <div class="layui-col-md3">司机</div>
                    <div class="layui-col-md3">时间</div>
                    <div class="layui-col-md3">预警类型</div>
                    <div class="layui-col-md12" id="msg">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


        </div>





<script src="/Public/Js/jquery.min.js"></script>
<script src="/Public/layui/layui.js"></script>
<script src="/Public/Js/office/mqttws31.js?version=<?php echo ($version); ?>"></script>

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