<?php
return array(
	//'配置项'=>'配置值'
    'URL_MODEL'         => '2',
    'MULTI_MODULE'      => true,//允许多模块
    'DEFAULT_MODULE'    => 'Office', // 默认模块
    'MODULE_ALLOW_LIST' => array('Office','Admin'),
    'AUTOLOAD_NAMESPACE'=> array('Lib'=>APP_PATH.'Lib'),
    'BAIDU_API_KEY'     => 'mbxCCTHApgXL9heLp0RMxOoY',

    //检查请求限制
    'CHECK_REQUEST'  => false,
    'IS_MIN'         => false,

    //下载文件地址
    'DOWNLOAD_FILE_PATH'   => '../api/',
);