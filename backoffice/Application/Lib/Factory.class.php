<?php
/**
 * Created by Wrf
 * User: Thinkpad
 * Date: 2017/1/10
 * Time: 11:33
 */

namespace Lib;

/**
 * 工厂类统一入口
*/

abstract class Factory
{
    /**
     * 全局配置对象
     *
     * @var $config
    */
    public static $config = null;

    /**
     * Global session object
     *
     * @var Session
    */
    public static $session = null;

    /**
     * Global context object
     * @var Context
    */

    public static $context = null;

    /**
     * @var sessionRegistry
    */
    public static $sessionRegistry = null;

    /**
     * 单例模式，获取 context 对象
     * @param $options array
     * @return Context
     */
    public static function getContext(array $options = array()){
        if(!self::$context){
            self::$context = self::createContext($options);
        }
        return self::$context;
    }

    protected static function createContext(array $options = array()){

        $context = Context::getInstance($options);

        return $context;
    }

    /**
     * 单例模式，获取sessionRegistry 对象
     * @param $identity string session唯一key
     * @return SessionRegistry
    */
    public static function getRegistry($identity){
        if(!self::$sessionRegistry){
            self::$sessionRegistry = self::createSessionRegistry($identity);
        }
        return self::$sessionRegistry;
    }

    protected static function createSessionRegistry($identity){
        return SessionRegistry::getInstance($identity);
    }
}