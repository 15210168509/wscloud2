<?php
/**
 * Created by PhpStorm.
 * User: WuRuifeng
 * Date: 14-12-16
 * Time: 下午2:48
 */
namespace Lib;

use Office\Model\ManagementLoginModel;
use Think\Controller;

/**
 * Class BaseController
 * @package Lib
 */
class BaseManagementController extends BaseController {

    /**
     * @var array $menu 用户所有的菜单
    */
    public $menu;
    /**
     * @var array $topMenu 顶部导航菜单
    */
    public $topMenu;
    /**
     * @var array $menu 展示侧边栏
    */
    public $leftMenu;

    /**
     * 实现父类 抽象方法 setOption
     *设置管理后台的超级sessionKey
    */
    public function setOptions(){
        $this->options = array('session_key'=>C('OfficeSessionKey'));
    }
    /**
     * 登陆处理，复写原父类中方法
     *
     * - 如果未登录，页面请求则跳转到登录画面，ajax请求则直接返回错误信息
     * - 如果以登录，则判断登录时间是否已过期
    */
    public function processLogin(){

        //初始化当前上下文信息，从session中获取登录用户数据
        $this->autoLogin();

        //需要登录
        if($this->authentication && !$this->context->loginuser->isLogin){

            if(IS_AJAX){
                die(array("status"=>"-1","msg"=>"登录过期或未登录，请重新登录"));
            }
            else{
                redirect(C('baseUrl').'/ManagementLogin');
            }
        }

    }

    public function getContextLogin()
    {
        return $this->context->loginuser;
    }

    /**
     *  cookie 自动登陆
     */
    public function autoLogin(){

    }
    /**
     * 复写父类中初始化头部信息相关操作
     *
     * -生成菜单
     * -定义MQTT配置信息
     * -获取当前登录账号的消息数
     * @return void
    */
    public function initHeader()
    {
        $this->getSession();
        //获取版本号，强制更新css和js
        $this->assign('version',C('VERSION'));
        //根据权限获取顶部菜单
        $this->topMenu = getTopMenu(explode(',',$this->context->loginuser->right),C('MENU'),CONTROLLER_NAME);

        //根据权限获取侧边菜单
        $this->leftMenu = getMenu(explode(',',$this->context->loginuser->right),$this->topMenu,CONTROLLER_NAME);

        //生成顶部菜单
        $topMenu  = openTopMenu($this->topMenu);

        //打开菜单
        $openMenu = openMenu($this->leftMenu,"/".CONTROLLER_NAME."/".ACTION_NAME);

        $this->assign('topMenu',$topMenu);
        $this->assign('menu',$openMenu);
        $this->assign('username',$this->context->loginuser->name);
        $this->assign('adminId',$this->context->loginuser->id);
        $this->assign('companyName',$this->context->loginuser->company_name);
        //MQTT服务器
        $this->assign('mqttServer',C('MQTT_HOST'));
        $this->assign('mqttServerPort',C('MQTT_HOST_PORT'));
        //用户订阅话题
        //todo:根据用户权限订阅不同话题
        $this->assign('adminTopic',C('ADMIN_TOPIC').'/'.$this->context->loginuser->phone);

        //报警是否弹框
        $result = D('System')->getSystemSetting($this->context->loginuser->company_id,CommonConst::SYSTEM_SET_WARNING_DIALOG);
        $this->assign('warningDialog',$result['data']['value']);



        //得到当前管理员消息个数
        $result = D('AdminMsg')->getAdminMsgCount(Status::USER_MSG_UNREAD);
        $this->assign('msgNum',$result['data']);
    }

    /**
     * 获取session
     */
    public function getSession()
    {
        $name = $_SESSION['login']->name;
        $id   = $_SESSION['login']->id;
        $this->assign('name',$name);
        $this->assign('id',$id);
    }

    /**
     *  用户操作权限检测
     * @param  int $action       权限名称
     * @return bool         返回结果
     */
    public function canDo($action){

        //权限拦截跳转
        $authority = explode(',', $this->context->loginuser->right);
        if (in_array($action, $authority)) {
            return true;
        } else {
            return false;
        }
    }
}