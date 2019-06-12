<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace TCT;
/**
 * 加密解密类
 */
class Des {

    private static $handler    =   ''; // 设置一个静态变量

    public static function init($type=''){ // 静态方法，参数为加密方式默认为空
        // 判断加密类型，如果没有指定加密类型则使用系统默认的加密方式
        $type   =   $type?$type:C('DATA_CRYPT_TYPE');
        // 判断加密方式是否有命名空间，如果有直接使用，如果没有则加上
        $class  =   strpos($type,'\\')? $type: 'Think\\Crypt\\Driver\\'. ucwords(strtolower($type));
        // 将加密方式存在静态变量中
        self::$handler  =    $class;
    }

    /**
     * 加密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @param integer $expire 有效期（秒） 0 为永久有效
     * @return string
     */
    public static function encrypt($data,$key,$expire=0){
        if(empty(self::$handler)){
            self::init();
        }
        $class  =   self::$handler; 
        return $class::encrypt($data,$key,$expire);
    }

    /**
     * 解密字符串
     * @param string $str 字符串
     * @param string $key 加密key
     * @return string
     */
    public static function decrypt($data,$key){
        if(empty(self::$handler)){
            self::init();
        }
        $class  =   self::$handler;         
        return $class::decrypt($data,$key);
    }
}