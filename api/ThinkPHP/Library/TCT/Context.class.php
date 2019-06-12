<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-11
 * Time: 下午3:01
 */

namespace Admin\Controller;
use Think\Controller;


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
    *
    */
    public function __construct(){
        $this->loginuser = new LoginuserModel();

        $this->loginuser->isLogin = isset($_SESSION["login"]);
        if($this->loginuser->isLogin){
            $this->loginuser->loginState = $_SESSION["login"]->data->loginState;
            $this->loginuser->usersId = $_SESSION["login"]->data->uid;
            $this->loginuser->bsId = $_SESSION["login"]->data->bsId;
            $this->loginuser->bsId = $_SESSION["login"]->data->bsId;
            $this->loginuser->empolyeeId = $_SESSION["login"]->data->employeesId;
            $this->loginuser->bsId = $_SESSION["login"]->data->bsId;
            $this->loginuser->user = $_SESSION["user"];
            $this->loginuser->userInfo = $_SESSION["userInfo"];
            $this->loginuser->bsInfo = $_SESSION["bsInfo"];
            $this->loginuser->right = $_SESSION["authority"];
            $this->loginuser->token = $_SESSION["token"];
            $this->loginuser->name = $_SESSION["userInfo"]->uRealName;
        }
    }

    /**
     * @return Context
     */
    public static function getContext()
    {
        if (!isset(self::$instance))
            self::$instance = new Context();
        return self::$instance;
    }
    
    /**
     * refrash Context
     */
    public static function frashContext(){
        return new Context();
    }

} 