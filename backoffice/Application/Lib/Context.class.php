<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-11
 * Time: 下午3:01
 */

namespace Lib;
use Think\Controller;
use Office\Model\LoginuserModel;

/**
 * Class Context
 * @package Home\Controller
 */
class Context{

    /**
     * Context 实体
    */
    protected static $instance;

    /**
     * @var LoginUserModel,用户登陆信息实例
    */
    public $loginuser = null;

    /**
     * @var array options 配置项
    */
    public $options = array();
    /**
    * @param array $options 配置选项
    */
    public function __construct(array $options = array()){

        $this->options = array_merge($this->options,$options);
        if(isset($_SESSION[$this->options['session_key']])){

            $this->loginuser = new \stdClass();

            foreach($_SESSION[$this->options['session_key']] as $key=>$val){

                $this->loginuser->$key = $val;
            }

            $this->loginuser->isLogin = isset($_SESSION[$this->options['session_key']])&&count($_SESSION[$this->options['session_key']])>4?true:false;
        }
    }


    /**
     * 返回全局context对象，只有在该对象不存在时才创建
     * @param array $options 配置选项
     * @return Context
     */
    public static function getInstance($options)
    {
        if (!isset(self::$instance))
            self::$instance = new Context($options);
        return self::$instance;
    }

    public function addKey($key,$value){
        //如果session不存在，则首先创建session
        if (!isset($_SESSION[$this->options['session_key']]) ) {
            $_SESSION[$this->options['session_key']] = array();
        }
        $_SESSION[$this->options['session_key']][$key]=$value;
    }

    public function createSession($data=null){
        if($data !=null){
            $rawData = isset($_SESSION[$this->options['session_key']])?$_SESSION[$this->options['session_key']]:array();
            if(is_object($data)){
                $data = json_decode(json_encode($data),true);
            }
            $_SESSION[$this->options['session_key']] = array_merge($rawData,$data);
        }
    }
    public function clearSession(){

        unset($_SESSION[$this->options['session_key']]);
    }
    /**
     * refrash Context
     */
    public static function frashContext(){
        return new Context();
    }

} 