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
    'OFFICE_HOST'       => 'http://localhost:89',
    'OPEN_ID_KEY'       => 'a5335e5c3471506c9d53fe9683b8f57d',
    'DB_CONFIG1'  => array(
        'db_type' => 'mysql',
        'db_user' => 'root',
        'db_pwd'  => 'huaxun',
        'db_host' => '192.168.1.148',
        'db_port' => '3306',
        'db_name' => 'wscloud'
    ),

    'DB_CONFIG2'  => array(
        'db_type' => 'mysql',
        'db_user' => 'root',
        'db_pwd'  => 'huaxun',
        'db_host' => '192.168.1.104',
        'db_port' => '3306',
        'db_name' => 'behavior'
    ),

    'DB_CONFIG3'  => array(
        'db_type' => 'mysql',
        'db_user' => 'test',
        'db_pwd'  => '123321',
        'db_host' => '47.94.214.22',
        'db_port' => '14306',
        'db_name' => 'videodb'
    ),

    //Redis缓存
    'DATA_CACHE_TYPE'    => 'Redis',
    'REDIS_HOST'         => '192.168.1.148',
    'REDIS_PORT'         => '6379',

    //MQTT服务器
    'MQTT_HOST'=>"tcp://192.168.1.148",
    'MQTT_HOST_PORT'=>61613,

    //检查请求限制
    'CHECK_REQUEST'      => false,

    //不验证token的接口
    'IGNORE_TOKEN'=>array('login','register','sendMobileVerificationCode','registerCompany','ajaxValidateMobileVerificationCode','refreshToken','loginByFace','findUpdatePassword','companyRegister','getAccessToken','appUpdateInfo','driverBehaviorMonitor','uploadTiredValue','driver','vehicle','testRedis','topic','getDriverCompanyInfo','getDeviceBandVehicleInfo','getDeviceSettingInfo','sendDeviceInfo','getDeviceSetting','setCompanyDeviceSetting','sendVehicleDeviceInstallPicture','cmd'),

    //当前环境
    'ENVIRONMENT'              => 'dev',//dev 开发模式，release 发布模式

);