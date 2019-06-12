<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-16
 * Time: 下午2:48
 */

namespace TCT;

use Lib\Factory;
use Lib\Registry;
use Think\Controller;
use Office\Controller\Context;
/**
 * Class BaseController
 * @package DDP
 */
class BaseManagementController extends Controller {

    /**
     * @var object Context 当前上下文环境，记录当前全局变量
     */
    protected $context;

    /**
     * @var array list of css files
     */
    public $css_files = array();

    /**
     * @var array list of javascript files
     */
    public $js_files = array();

    public $js_all_files = array();

    /**
     * @var array breadcrumb
     */
    public $breadcrumb = array();

    /**
     * @var array  显示的菜单
     */
    public $menu = array();

    /**
     * @var array   访问页面的权限
     */
    public $authority_url = array();

    /**
     * @var bool 是否需要登陆认证，默认为true，需要。
     */
    public $authentication = true;

    /**
     * @var string 控制器唯一表示
    */
    public $identity;

    /**
     * @var array 类中所有的method集合
    */
    public $methods;

    /**
     * @var mixed 保留状态
     *
    */
    public $state;


    public function  _initialize(){

        $this->identity = $this->getName();

        $this->methods = array();

        $this->state = new Registry();

        //从session中获取当前保留的状态
        if(isset($_SESSION['jinan'][$this->identity])&&!empty($_SESSION['jinan'][$this->identity])){

            $data = $_SESSION['jinan'][$this->identity];
            $data = base64_decode($data);
            $this->state = unserialize($data);
        }

        //初始化当前上下文信息，从session中获取登录用户数据等
        $this->autoLogin();

        //需要登录
        if($this->authentication && !$this->context->loginuser->isLogin){
            //todo:用户登录以及安全验证
            if(IS_AJAX){
                die(array("status"=>"-1","msg"=>"登录过期或未登录，请重新登录"));
            }
            else{
                redirect('/ManagementLogin/index');
            }

            //查找页面访问权限
            $this->canOpen();
        }
    }

    /**
     *  cookie 自动登陆
     */
    public function autoLogin(){

        $this->context = Factory::getContext();

        //如果当前用户已登录，直接返回
        if($this->context->loginuser->isLogin){
            return true;
        }

        //如果没有登录，检查cookie。浏览器端没有cookie，自动返回
        if(!isset($_COOKIE["tct"]))
        {
            return false;

        }else{
            //cookie值存在，尝试解析cookie
            $des = new Des();
            $va = $des->decrypt($_COOKIE["tct"], TCTConst::LOGIN_DES_KEY, true);
            $va = unserialize($va);
            //解析失败，返回
            if(!$va){
                return false;
            }
            //尝试登录
            $oa = D('ManagementLogin');
            $_res = $oa->login($va->account, $va->pass);
            if($_res->res){
                $this->context = Context::frashContext();
                return true;
            }else{
                return false;
            }

        }


    }
    /**
     * @return bool 页面访问权限更新
     */
    public function canOpen(){

        $this->authority_url = getMenu($this->context->loginuser->right,C('authority_url'));

        foreach($this->authority_url as $key=>$value){
            if(in_array("/".CONTROLLER_NAME."/".ACTION_NAME,$value)){
                return;
            }
        }
        redirect('/Office/Login',3,'您无访问此页面的权限，3秒后跳转至登录');
    }
    /**
     *  用户操作权限检测
     * @param $action_name  权限名称
     * @return bool         返回结果
     */
    public function canDo($action){
        
        //权限拦截跳转
        $authority = $this->context->loginuser->right;
        if (in_array($action, $authority)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 添加css样式到页面
     * @param $css_uri
     * @param string $css_media_type
     * @param null $offset
     */
    public function addCSS($css_uri, $css_media_type = 'all', $offset = null)
    {
        if (!is_array($css_uri))
            $css_uri = array($css_uri=>$css_media_type);

        if (!is_array($css_uri))
            $css_uri = array($css_uri);

        foreach ($css_uri as $css_file => $media)
        {
            if (!isset($this->css_files[$css_file]))
            {
                $size = count($this->css_files);
                if ($offset === null || $offset > $size || $offset < 0 || !is_numeric($offset))
                    $offset = $size;
                $this->css_files = array_merge(array_slice($this->css_files, 0, $offset), array($css_file=>$media), array_slice($this->css_files, $offset));
            }
        }
    }

    /**
     * 移除css样式
     * @param $css_uri
     * @param string $css_media_type
     */
    public function removeCSS($css_uri, $css_media_type = 'all')
    {
        if (!is_array($css_uri))
            $css_uri = array($css_uri=>$css_media_type);

        foreach ($css_uri as $css_file => $media)
        {
            if (isset($this->css_files[key($css_file)]))
                unset($this->css_files[key($css_file)]);
        }
    }

    /**
     *  添加js文件到页面
     * @param mixed $js_uri
     * @return void
     */
    public function addJS($js_uri)
    {
        if (is_array($js_uri)){
            foreach ($js_uri as $js_file)
            {
                $key = is_array($js_file) ? key($js_file) : $js_file;
                if (!in_array($js_file, $this->js_files))
                    $this->js_files[] = $js_file;
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->js_files))
                $this->js_files[] = $js_uri;
        }
    }

    /**
     * @param $js_uri
     */
    public function removeJS($js_uri)
    {
        if (is_array($js_uri))
            foreach ($js_uri as $js_file)
            {
                if ($js_file && in_array($js_file, $this->js_files))
                    unset($this->js_files[array_search($js_file,$this->js_files)]);
            }
        else
        {
            if ($js_uri)
                unset($this->js_files[array_search($js_uri,$this->js_files)]);
        }
    }



    /**
     * Add a new javascript file in page header.
     *
     * @param mixed $js_uri
     * @return void
     */
    public function addAllJS($js_uri)
    {
        if (is_array($js_uri)){
            foreach ($js_uri as $js_file)
            {
                $key = is_array($js_file) ? key($js_file) : $js_file;
                if (!in_array($js_file, $this->js_all_files))
                    $this->js_all_files[] = $js_file;
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->js_all_files))
                $this->js_all_files[] = $js_uri;
        }
    }

    /**
     * @param string $templateFile
     * @param string $charset
     * @param string $contentType
     * @param string $content
     * @param string $prefix
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix=''){

        $this->assign("css_files",$this->css_files);
        $this->assign("js_all_files",$this->js_all_files);
        $this->assign("js_files",$this->js_files);
        $this->assign("breadcrumb",$this->breadcrumb);
        $this->initHeader();
        $this->initContent();
        $this->initFooter();
        $this->getSession();
        var_dump($this->context->loginuser->right);
        $this->menu = getMenu($this->context->loginuser->right,C('MENU'));
        //$this->menu = C('MENU');
        //打开菜单
        $openMenu = openMenu($this->menu,"/".CONTROLLER_NAME."/".ACTION_NAME);
        $this->assign('menu',$openMenu);
        $this->assign('username',$this->context->loginuser->name);

        //输出页面
        parent::display($templateFile,$charset,$contentType,$content,$prefix);

    }

    /**
     *初始化页面头部信息
     */
    public function initHeader(){

        if($this->context->user->logged){
            $this->assign("logged","logged");
            $this->assign("username",$this->context->user->uNickName);
            $this->assign("uid",$this->context->user->id);
        }
    }

    /**
     *
     */
    public function initContent(){

    }

    /**
     *初始化页面底部信息
     */
    public function initFooter(){

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

    public function getName()
    {
        if (empty($this->identity))
        {
            $r = null;

            if (!preg_match('/(.*)Controller/i', get_class($this), $r))
            {
                throw new \Exception('获取控制器名称错误', 500);
            }

            $this->identity = strtolower(base64_encode($r[1]));
        }

        return $this->identity;
    }

    public function _afterAll(){
        //session 处理
        $data = serialize($this->state);
        $_SESSION['jinan'][$this->identity] = base64_encode($data);
        //$data = base64_decode($data);
        //$this->state = unserialize($data);
    }
}