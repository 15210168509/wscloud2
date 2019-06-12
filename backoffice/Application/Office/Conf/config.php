<?php
/**
 * Created by PhpSrtom.
 * User: ye
 * Date: 14-10-28
 * Time: ����3:01
 */
return array(

    //==============基础配置======================
    'VERSION'             => '0.03',
    'baseUrl'             => 'http://localhost:108/Office',
    'API_SERVER'          => 'http://localhost:133/Home',
    'Debug'               => true,
    'TOKEN_TYPE'          => 10,
    'ENV'                 => 'dev',//dev 开发模式，release 发布模式
    'ADMIN_TOPIC'         => 'office',
    'OfficeSessionKey'    => 'wsoffice',
    'OfficeCookieKey'     => 'wsoffice',
    'OfficeDesKey'        => 'd4fspemgkxuamfdsfds',
    'VIEW_PATH'           => 'Theme/default/office/',
    'CLIENT_TICKET_KEY'   => 'dsfdsadflj892s34jds89240a9udiuj18iivvfvdvvzhvnaiuehf93',
    'TIRED_WARNING_NUMBER'=> 70,

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
    'MQTT_HOST'           => '192.168.1.148',//MQTT服务器地址
    'MQTT_HOST_PORT'      => 61623,//MQTT服务器地址监听端口

    //==============日志服务=======================
    'LOGGER_API'          => 'http://logger.56xun.cn/servers.php',
    'LOGGER_CLIENT_ID'    => 'cloud-monitoring-5Zhbsi9RHC',
    'LOGGER_SECRET'       => '7fb6446209d1a4d13928ee09815090fbccd6836c6f6a81f2794b123851aba083',
    'LOGGER_PROJECT'      => 'micro-view',
    'LOGGER_LOGSTORE'     => 'cloud-monitoring',
    'LOGGER_LOWEST_LEVEL' => 'info',

    'DOWNLOAD_FILE_PATH' => '../api/',

    //==============模板配置========================
    'MENU'=>array(
        array('id'=>1,'name'=>'系统管理','url'=>'/Monitor/showData','controller'=>array('Index','Admin','Company','Device','Vehicle','Driver','Groups','Monitor','RoadLine','Stat'),'submenu'=>array(

            array('id'=>100,'pid'=>0,'level'=>100,'name'=>'管理员管理','url'=>array("#")),
            array('id'=>110,'pid'=>100,'level'=>110,'name'=>'添加管理员','url'=>array("/Admin/add")),
            array('id'=>120,'pid'=>100,'level'=>120,'name'=>'管理员列表','url'=>array("/Admin/lists")),

            array('id'=>800,'pid'=>0,'level'=>800,'name'=>'企业组织管理','url'=>array("#")),
            array('id'=>810,'pid'=>800,'level'=>810,'name'=>'添加组织','url'=>array("/Company/add")),
            array('id'=>820,'pid'=>800,'level'=>820,'name'=>'组织列表','url'=>array("/Company/lists")),


            array('id'=>200,'pid'=>0,'level'=>200,'name'=>'设备管理','url'=>array("#")),
            array('id'=>210,'pid'=>200,'level'=>210,'name'=>'添加设备','url'=>array("/Device/add")),
            array('id'=>220,'pid'=>200,'level'=>220,'name'=>'设备列表','url'=>array("/Device/lists")),
            array('id'=>230,'pid'=>200,'level'=>230,'name'=>'设备配置','url'=>array("/Device/addDeviceSetting")),

            array('id'=>300,'pid'=>0,'level'=>300,'name'=>'车辆管理','url'=>array("#")),
            array('id'=>310,'pid'=>300,'level'=>310,'name'=>'添加车辆','url'=>array("/Vehicle/add")),
            array('id'=>320,'pid'=>300,'level'=>320,'name'=>'车辆列表','url'=>array("/Vehicle/lists")),

            array('id'=>400,'pid'=>0,'level'=>400,'name'=>'司机管理','url'=>array("#")),
            array('id'=>410,'pid'=>400,'level'=>410,'name'=>'添加司机','url'=>array("/Driver/add")),
            array('id'=>420,'pid'=>400,'level'=>420,'name'=>'司机列表','url'=>array("/Driver/lists")),
            array('id'=>430,'pid'=>400,'level'=>430,'name'=>'监控列表','url'=>array("/Driver/behaviorLists")),

            array('id'=>500,'pid'=>0,'level'=>500,'name'=>'分组管理','url'=>array("#")),
            array('id'=>510,'pid'=>500,'level'=>510,'name'=>'添加分组','url'=>array("/Groups/add")),
            array('id'=>520,'pid'=>500,'level'=>520,'name'=>'分组列表','url'=>array("/Groups/lists")),

            array('id'=>600,'pid'=>0,'level'=>600,'name'=>'监控中心','url'=>array("#")),
            array('id'=>610,'pid'=>600,'level'=>610,'name'=>'数据分析','url'=>array("/Monitor/showData")),
            array('id'=>620,'pid'=>600,'level'=>620,'name'=>'数据大屏','url'=>array("/Monitor/realTimeData"),'openNew'=>true),

            array('id'=>700,'pid'=>0,'level'=>700,'name'=>'规划路线','url'=>array("#")),
            array('id'=>710,'pid'=>700,'level'=>710,'name'=>'制定路线','url'=>array("/RoadLine/add")),
            array('id'=>720,'pid'=>700,'level'=>720,'name'=>'追踪路线','url'=>array("/RoadLine/roadLineMonitor")),
            array('id'=>730,'pid'=>700,'level'=>730,'name'=>'轨迹回放','url'=>array("/RoadLine/historyLine")),

            array('id'=>900,'pid'=>0,'level'=>900,'name'=>'统计报表','url'=>array("#")),
            array('id'=>910,'pid'=>900,'level'=>910,'name'=>'预警统计','url'=>array("/Stat/warnStat")),
            array('id'=>920,'pid'=>900,'level'=>920,'name'=>'上下线统计','url'=>array("/Stat/onlineStat"))
        )),

        array('id'=>2,'name'=>'设置','url'=>'/Office/System','controller'=>array('System','Device'),'submenu'=>array(

            array('id'=>1000,'pid'=>0,'level'=>1000,'name'=>'系统设置','url'=>array("#")),
            array('id'=>1010,'pid'=>1000,'level'=>1010,'name'=>'预警设置','url'=>array("/System/index")),

        )),

    ),
    'RIGHT'=>array(
        array('name'=>' 系统管理', 'right'=>1, 'children'=>array(
            array('name'=>' 管理员管理', 'right'=>100, 'children'=>array(
                array('name'=>'添加管理员', 'right'=>110, 'children'=>array(), 'operations'=>array()),
                array('name'=>'管理员列表', 'right'=>120, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>' 企业组织管理', 'right'=>800, 'children'=>array(
                array('name'=>'添加企业组织', 'right'=>810, 'children'=>array(), 'operations'=>array()),
                array('name'=>'企业组织列表', 'right'=>820, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'设备管理', 'right'=>200, 'children'=>array(
                array('name'=>'添加设备', 'right'=>210, 'children'=>array(), 'operations'=>array()),
                array('name'=>'设备列表', 'right'=>220, 'children'=>array(), 'operations'=>array()),
                array('name'=>'设备配置', 'right'=>230, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'车辆管理', 'right'=>300, 'children'=>array(
                array('name'=>'添加车辆', 'right'=>310, 'children'=>array(), 'operations'=>array()),
                array('name'=>'车辆列表', 'right'=>320, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'司机管理', 'right'=>400, 'children'=>array(
                array('name'=>'添加司机', 'right'=>410, 'children'=>array(), 'operations'=>array()),
                array('name'=>'司机列表', 'right'=>420, 'children'=>array(), 'operations'=>array()),
                array('name'=>'监控列表', 'right'=>430, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'分组管理', 'right'=>500, 'children'=>array(
                array('name'=>'添加分组', 'right'=>510, 'children'=>array(), 'operations'=>array()),
                array('name'=>'分组列表', 'right'=>520, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'监控中心', 'right'=>600, 'children'=>array(
                array('name'=>'数据分析', 'right'=>610, 'children'=>array(), 'operations'=>array()),
                array('name'=>'数据大屏', 'right'=>620, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'规划路线', 'right'=>700, 'children'=>array(
                array('name'=>'制定路线', 'right'=>710, 'children'=>array(), 'operations'=>array()),
                array('name'=>'追踪路线', 'right'=>720, 'children'=>array(), 'operations'=>array()),
                array('name'=>'轨迹回放', 'right'=>730, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

            array('name'=>'统计报表', 'right'=>900, 'children'=>array(
                array('name'=>'预警统计', 'right'=>910, 'children'=>array(), 'operations'=>array()),
                array('name'=>'上下线统计', 'right'=>920, 'children'=>array(), 'operations'=>array())
            ), 'operations'=>array()),

        ), 'operations'=>array()),

        array('name'=>'设置', 'right'=>2, 'children'=>array(

            array('name'=>' 系统设置', 'right'=>1000, 'children'=>array(
                array('name'=>'预警设置', 'right'=>1010, 'children'=>array(), 'operations'=>array()),
            ), 'operations'=>array()),

        ), 'operations'=>array()),

    )
);