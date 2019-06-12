<?php
return array(
	//'配置项'=>'配置值'
    'DEBUG'             => true,
    'URL_MODEL'         => '2',
    'MULTI_MODULE'      => true,
    'MODULE_ALLOW_LIST' => array('Home'),
    'AUTOLOAD_NAMESPACE'=> array('Lib'=>APP_PATH.'Lib'),
    'DEFAULT_MODULE'    => 'Home', // 默认模块
    'API_STRICT_MODE'   => false,//签名验证开启
    'OFFICE_HOST'       => 'http://safeoffice.56xun.cn',
    'OPEN_ID_KEY'       => 'a5335e5c3471506c9d53fe9683b8f57d',
    'DB_CONFIG1'  => array(
        'db_type' => 'mysql',
        'db_user' => '@db1_user@',
        'db_pwd'  => '@db1_pwd@',
        'db_host' => '@db1_host@',
        'db_port' => '@db1_port@',
        'db_name' => '@db1_name@'
    ),

    'DB_CONFIG2'  => array(
        'db_type' => 'mysql',
        'db_user' => '@db2_user@',
        'db_pwd'  => '@db2_pwd@',
        'db_host' => '@db2_host@',
        'db_port' => '@db2_port@',
        'db_name' => '@db2_name@'
    ),

    //Redis缓存
    'DATA_CACHE_TYPE'    => 'Redis',
    'REDIS_HOST'         => '@redisHost@',
    'REDIS_PORT'         => '@redisPort@',

    //MQTT服务器
    'MQTT_HOST'=>"@mqttHost@",
    'MQTT_HOST_PORT'=>"@mqttPort@",

    //检查请求限制
    'CHECK_REQUEST'      => false,

    //不验证token的接口
    'IGNORE_TOKEN'=>array('login','register','sendMobileVerificationCode','registerCompany','ajaxValidateMobileVerificationCode','refreshToken','loginByFace','findUpdatePassword','companyRegister','getAccessToken','appUpdateInfo','driverBehaviorMonitor','uploadTiredValue','driver','vehicle','testRedis','topic','getDriverCompanyInfo','getDeviceBandVehicleInfo'),
);