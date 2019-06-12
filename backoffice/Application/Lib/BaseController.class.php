<?php
/**
 * Created by PhpStorm.
 * User: WuRuifeng
 * Date: 14-12-16
 * Time: 下午2:48
 */
namespace Lib;

use Think\Controller;

/**
 * 前端控制基础类
 * @package Lib
 */
abstract class BaseController extends Controller {

    /**
     * @var object Context 当前上下文环境，记录当前全局变量
     */
    protected $context;

    /**
     * @var object Registry 当前注册表对象，用于数据存储
    */
    protected $registry;
    /**
     * @var array list of css files，
     */
    public $css_files = array();

    /**
     * @var array 内部引用js文件
     */
    public $js_files = array();
    public $model_js_files = array();

    /**
     * @var array 第三方外部js文件，如：http://map.baidu.com/getmap.js
    */
    public $js_all_files = array();

    /**
     * @var array 面包屑导航
    */
    public $breadcrumb = array();
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
     * @var array options 上下文初始化配置
    */

    public $options = array();


    public function  _initialize(){

        //获取当前存储的key
        $this->identity = $this->getName();
        $this->methods = array();
        //上下文初始化配置
        $this->setOptions();
        $this->context  = Factory::getContext($this->options);
        $this->registry = Factory::getRegistry($this->identity);
        $this->processLogin();
    }

    public function setState($key,$val){
        $this->registry->set($key,$val);
    }
    public function removeState($key){
        if(isset($this->registry->$key)){
            unset($this->registry->$key);
        }
    }
    public function getState($key){
        return $this->registry->get($key);

    }
    /**
     * 当前上下文配置信息
     * 由具体子类实现
    */
    abstract public function setOptions();
    /**
     * 登陆处理
    */
    abstract public function processLogin();
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
            //是否加载压缩文件
            if (C('IS_MIN') == 'true') {
                $css_file = str_replace(".min","",$css_file);
                $css_file = str_replace(".css",".min.css",$css_file);
            }

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
                if (!in_array($js_file, $this->js_files)){
                    if (C('IS_MIN') == 'true') {
                        $js_file = str_replace(".min","",$js_file);
                        $js_file = str_replace(".js",".min.js",$js_file);
                        $this->js_files[] = $js_file;
                    } else {
                        $this->js_files[] = $js_file;
                    }
                }
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->js_files))
                $this->js_files[] = $js_uri;
        }
    }

    public function addModelJS($js_uri)
    {
        if (is_array($js_uri)){
            foreach ($js_uri as $js_file)
            {
                $key = is_array($js_file) ? key($js_file) : $js_file;
                if (!in_array($js_file, $this->model_js_files)){
                    if (C('IS_MIN') == 'true') {
                        $js_file = str_replace(".min","",$js_file);
                        $js_file = str_replace(".js",".min.js",$js_file);
                        $this->model_js_files[] = $js_file;
                    } else {
                        $js_file = str_replace(".js","",$js_file);
                        $this->model_js_files[] = $js_file;
                    }
                }
            }
        }
        else
        {
            if ($js_uri && !in_array($js_uri, $this->model_js_files))
                $this->model_js_files[] = $js_uri;
        }
    }

    /**
     * 移除js
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
     * 加载第三方js类库
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
     * 修改面包屑内容
    */
    public function addBreadCrumb($breadCrumb = array()){

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
        $this->assign('model_js_files',$this->model_js_files);
        $this->assign('baseUrl',C('baseUrl'));
        $this->initHeader();
        $this->initContent();
        $this->initFooter();
        //输出页面
        parent::display($templateFile,$charset,$contentType,$content,$prefix);

    }

    /**
     *初始化页面头部信息
     */
    public function initHeader(){

    }

    /**
     *初始化页面内容信息
     */
    public function initContent(){

    }

    /**
     *初始化页面底部信息
     */
    public function initFooter(){

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
}