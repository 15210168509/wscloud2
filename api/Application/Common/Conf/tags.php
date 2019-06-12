<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/4/6
 * Time: 10:56
 */
return array(
    'url_dispatch'  => array(
        'Lib\Behavior\SignCheckBehavior', //接口签名验证
        'Lib\Behavior\TokenCheckBehavior',//接口用户token验证
        'Lib\Behavior\MethodCheckBehavior' //接口请求参数验证
    )
);