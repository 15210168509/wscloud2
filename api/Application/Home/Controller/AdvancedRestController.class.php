<?php

namespace Home\Controller;
use Lib\Tools;
use Think\Controller\RestController;
use Lib\Registry;

class AdvancedRestController extends RestController
{
    /**
     * @var array 接口返回值
     */
    private $_returnVal = array();

    /**
     * @var bool 参数验证通过标记，默认为通过
     */
    private $_passParamValidate = true;

    /**
     * @var string 参数未通过信息容器
     */
    private $_paramError    = '';           //错误消息
    private $_paramErrorCode = '';          //错误编号

    /**
     * @var mixed 错误信息容器
     *
     */
    private $_errors;

    public function __construct()
    {
        parent::__construct();
        //初始化容器，注册表类型
        $this->_errors = new Registry();
    }
    public function setError($type,$error)
    {
        $this->_errors->set($type,$error);
    }
    public function getError($type,$default = array())
    {
        return $this->_errors->get($type,$default);
    }
    public function setReturnVal($code,$msg,$statusCode,$data = null)
    {
        if ($data === null) {
            $this->_returnVal = array('code'=>$code,'msg'=>$msg,'status_code'=>$statusCode);
        } else {
            $this->_returnVal = array('code'=>$code,'msg'=>$msg,'status_code'=>$statusCode,'data'=>$data);
        }

    }
    public function getReturnVal()
    {
        return $this->_returnVal;
    }
    public function restReturn()
    {
        $this->ajaxReturn($this->_returnVal);
    }
    public function validateParams($condition = array())
    {
        //预设参数判断
        if (is_array($condition) && !empty($condition)) {
            $_validate = $condition;
        }

        if (isset($_validate)) {
            //参数验证开始
            foreach ($_validate as $item) {
                if (is_array($item)) {

                    if (!$this->_passParamValidate) {

                        //已有参数未通过验证,则不再验证后续参数
                        return $this->_passParamValidate;
                    }
                    if (false === $this->_validateItem($item)) {

                        //参数未通过验证
                        $this->_passParamValidate = false;
                        //设置返回预设错误信息
                        $this->setParamError($item[1]);
                        //设置错误状态编号
                        $this->setParamErrorCode($item[2]);
                    }
                }
            }
        }
        return $this->_passParamValidate;

    }
     function _validateItem($item){
         switch (strtolower(trim($item[3]))) {
             case 'function'://使用函数验证
                 return call_user_func($item[4],$item[0]);
             case 'callback'://使用回调方法验证
                 return call_user_func($item[4],$item[0]);
             case 'empty':
                 return !(empty($item[0]) || $item[0]=='' || $item[0] === null);
             case 'phone':
                 return Tools::checkPhone($item[0]);
             case 'number':
                 return Tools::checkNumber($item[0]) && (isset($item[4])?strlen($item[0])==$item[4]:true);
             default:
                 return false;
         }
    }
    public function setParamError($msg)
    {
        $this->_paramError = $msg;
    }
    public function getParamError()
    {
        return $this->_paramError;
    }
    public function setParamErrorCode($code){
        $this->_paramErrorCode = $code;
    }
    public function getParamErroCode(){
        return $this->_paramErrorCode;
    }
    public function hook($hookName,$params = array())
    {
        $actions = C(ucfirst($hookName).'Hook');
        if (is_array($actions)) {
            foreach($actions as $action){
                $class= '\Lib\Hook\\'.ucfirst($hookName).'\\'.$action.'Hook';
                if (class_exists($class)) {
                    $object = new $class();
                    $classReflection  =   new \ReflectionClass($object);
                    //执行run操作
                    if($classReflection->hasMethod('run')){
                        $defaultMethod = $classReflection->getMethod('run');
                        $defaultMethod->invokeArgs($object,$params);
                    }else{

                    }

                }else{

                }
            }
        }
    }

}