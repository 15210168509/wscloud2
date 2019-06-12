<?php
return array(

    //==============基础配置======================
    'appid'               => 'wx7585ab4e7e0233f1',
    'secret'              => "229440e277760dec72608c74cf672fd7",
    'CLIENT_TICKET_KEY'   => 'dsfdsadflj892s34jds89240a9udiuj18iivvfvnaiuehf93', // 客户数据基础密钥
    'ENV'                 => 'dev',//dev 开发模式，release 发布模式
    'endPoint'            =>'http://1997387903951331.mns.cn-hangzhou.aliyuncs.com/',
    'accessId'            =>'LTAIZY6s5x0BlCeF',
    'accessKey'           =>'FgDmF1yixzeYj3BQwtk93seUVyxhuP',



    //==============日志服务=======================
    'LOGGER_API'          => 'http://logger.56xun.cn/servers.php',
    'LOGGER_CLIENT_ID'    => 'cloud-monitoring-api-U2FsdGVkX1',
    'LOGGER_SECRET'       => '367bcd3f252775728340a9bfa6b96104e768276aae67800c90d96cef23f4dd8f',
    'LOGGER_PROJECT'      => 'micro-view',
    'LOGGER_LOGSTORE'     => 'cloud-monitoring-api',
    'LOGGER_LOWEST_LEVEL' => 'info',

    //==============对象存储=======================
    'IMG_FILE_SERVER'     => 'http://vshi-img.oss-cn-beijing.aliyuncs.com',
    'NV21_FILE_SERVER'    => 'http://vshi-nv21.oss-cn-beijing.aliyuncs.com',
    'VIDEO_FILE_SERVER'   => 'http://vshi-video.oss-cn-beijing.aliyuncs.com',
    'PRO_FILE_SERVER'     => 'http://vshi-profile.oss-cn-beijing.aliyuncs.com',

    //================aliyun OSS==================
    'OSS_DRIVER_FACE_IMG_PATH' => 'http://vshi-baidu-face.oss-cn-beijing.aliyuncs.com/',
    //================极光推送 锐承==================
    'JPUSH_APP_KEY'     => '6e6b08b95d97423fec749bc2',
    'JPUSH_MASTER_SECRET'=> "dfbe79e2824321ea9f9827c4",
    //================极光推送 瑞联==================
    'JPUSH_APP_KEY2'     => 'fca0048599b06f72189499b8',
    'JPUSH_MASTER_SECRET2'=> "8c52259819267a1105ba868d",

    //================设备默认配置==================
    'DEVICE_SETTING'=>array(
        '10'=>1,//低速报警
        '20'=>1,//警报声音
        '30'=>1,//抽烟报警
        '40'=>1,//打电话报警
        '50'=>1,//左顾右盼报警
        '60'=>-20,//低头角度
        '70'=>2,//左顾右盼延时
        '80'=>-25,//左顾角度
        '90'=>25,//右盼角度
        '100'=>1,//闭眼延时
        '110'=>5,//低头报警间隔
        '120'=>2,//闭眼报警间隔
        '130'=>5,//打哈欠报警间隔
        '140'=>8,//抽烟报警间隔
        '150'=>8,//打电话报警间隔
        '160'=>5,//左顾右盼报警间隔
        '170'=>1,//抽烟延时
        '180'=>1,//打电话延时
        '190'=>1,//低头延时
    )
);