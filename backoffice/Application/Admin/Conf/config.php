<?php
/**
 * Created by PhpSrtom.
 * User: ye
 * Date: 14-10-28
 * Time: ����3:01
 */
return array(

    //==============基础配置======================
    'VERSION'             => '0.01',
    'baseUrl'             => 'http://localhost:108/Admin',
    'API_SERVER'          => 'http://localhost:133/Home',
    'Debug'               => true,
    'TOKEN_TYPE'          => 30,
    'ENV'                 => 'dev',//dev 开发模式，release 发布模式
    'ADMIN_TOPIC'         => 'manager',
    'OfficeSessionKey'    => 'safeoffice',
    'OfficeCookieKey'     => 'safeoffice',
    'OfficeDesKey'        => 'd4fspemgkxuamfdsfds',
    'VIEW_PATH'           => 'Theme/admin/',
    'CLIENT_TICKET_KEY'   => 'dsfdsadflj892s34jds89240a9udiuj18iivvfvdvvzhvnaiuehf93',

    //==============API配置=======================
    'API_GATEWAY_KEY'     => '24859091',//appkey
    'API_GATEWAY_SECRET'  => '46e65e3bafa12d58ea9f113b02410e95',//appsecret
    'API_GATEWAY_ENV'     => 1,//API环境，1：测试，2：预发，3：发布
    'API_LOGGER'          => true,//API接口请求日志
    'ServicePrefix'       => null,//接口控制器替换，无此项设置为null

    //==============对象存储=======================
    'IMG_FILE_SERVER'     => 'http://vshi-img.oss-cn-beijing.aliyuncs.com',
    'NV21_FILE_SERVER'    => 'http://vshi-nv21.oss-cn-beijing.aliyuncs.com',
    'VIDEO_FILE_SERVER'   => 'http://vshi-video.oss-cn-beijing.aliyuncs.com',
    'PRO_FILE_SERVER'     => 'http://vshi-profile.oss-cn-beijing.aliyuncs.com',

    //==============MQTT==========================
    'MQTT_HOST'           => '192.168.1.143',//MQTT服务器地址
    'MQTT_HOST_PORT'      => 61623,//MQTT服务器地址监听端口

    //==============日志服务=======================
    'LOGGER_API'          => 'http://logger.56xun.cn/servers.php',
    'LOGGER_CLIENT_ID'    => 'cloud-monitoring-5Zhbsi9RHC',
    'LOGGER_SECRET'       => '7fb6446209d1a4d13928ee09815090fbccd6836c6f6a81f2794b123851aba083',
    'LOGGER_PROJECT'      => 'micro-view',
    'LOGGER_LOGSTORE'     => 'cloud-monitoring',
    'LOGGER_LOWEST_LEVEL' => 'info',

    //==============模板配置========================
    'MENU'=>array(
        array('id'=>1,'name'=>'控制台','url'=>'/Index/index','controller'=>array('Index','Admin','Device','Driver','Company','Vehicle','Behavior'),'submenu'=>array(

            array('id'=>100,'pid'=>0,'level'=>100,'name'=>'管理员管理','url'=>array("#")),
            array('id'=>110,'pid'=>100,'level'=>110,'name'=>'添加管理员','url'=>array("/Admin/add")),
            array('id'=>120,'pid'=>100,'level'=>120,'name'=>'管理员列表','url'=>array("/Admin/lists")),

            array('id'=>400,'pid'=>0,'level'=>400,'name'=>'公司管理','url'=>array("#")),
            array('id'=>410,'pid'=>400,'level'=>410,'name'=>'公司列表','url'=>array("/Company/lists")),
            array('id'=>420,'pid'=>400,'level'=>420,'name'=>'添加公司','url'=>array("/Company/add")),

            array('id'=>200,'pid'=>0,'level'=>200,'name'=>'设备管理','url'=>array("#")),
            array('id'=>210,'pid'=>200,'level'=>210,'name'=>'设备列表','url'=>array("/Device/lists")),


            array('id'=>300,'pid'=>0,'level'=>300,'name'=>'司机管理','url'=>array("#")),
            array('id'=>310,'pid'=>300,'level'=>310,'name'=>'司机列表','url'=>array("/Driver/lists")),

            array('id'=>500,'pid'=>0,'level'=>500,'name'=>'车辆管理','url'=>array("#")),
            array('id'=>510,'pid'=>500,'level'=>510,'name'=>'车辆列表','url'=>array("/Vehicle/lists")),

            array('id'=>600,'pid'=>0,'level'=>600,'name'=>'预警管理','url'=>array("#")),
            array('id'=>610,'pid'=>600,'level'=>610,'name'=>'预警列表','url'=>array("/Behavior/lists")),

           )),

    ),
    'RIGHT'=>array(
        array('name'=>' 控制台', 'right'=>1, 'children'=>array(
            array('name'=>' 管理员管理', 'right'=>100, 'children'=>array(
                array('name'=>'添加管理员', 'right'=>110, 'children'=>array(), 'operations'=>array()),
                array('name'=>'管理员列表', 'right'=>120, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'设备管理', 'right'=>200, 'children'=>array(
                array('name'=>'设备列表', 'right'=>210, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'司机管理', 'right'=>300, 'children'=>array(
                array('name'=>'司机列表', 'right'=>310, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'公司管理', 'right'=>400, 'children'=>array(
                array('name'=>'公司列表', 'right'=>410, 'children'=>array(), 'operations'=>array()),
                array('name'=>'添加公司', 'right'=>420, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'车辆管理', 'right'=>500, 'children'=>array(
                array('name'=>'车辆列表', 'right'=>510, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'预警管理', 'right'=>600, 'children'=>array(
                array('name'=>'预警列表', 'right'=>610, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),


        ), 'operations'=>array()),


    )
);