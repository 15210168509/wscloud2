<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Lib\Behavior;
use Lib\Auth;
use Lib\Code;
use Lib\CommonConst;
use Lib\DocParser;
use Lib\Msg;
use Lib\StatusCode;
use Lib\TaskQueue\TaskQueue;
use Think\Behavior;
/**
 * 行为扩展：签名检测
 */
class TokenCheckBehavior extends Behavior{
    public function run(&$params) {

        //获取控制器完整类名
        $className = $this->getClassName(CONTROLLER_NAME);
        //获取类结构描述
        $classDes   = new \ReflectionClass($className);
        //采用注释方法获取访问权限
        $method = $classDes->getmethod(ACTION_NAME);
        //是否验证token
        if (!in_array($method->name,C('IGNORE_TOKEN')) && $className != 'Home\Controller\TestController'){
            $auth = new Auth();
            $result = $auth->checkToken($_GET['token']);

            if($result == CommonConst::OTHER_DEVICE_LOGIN){
                $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::OTHER_DEVICE_LOGIN,'status_code'=>StatusCode::OTHER_DEVICE_LOGIN));
            } else if (!$result){
                $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>Msg::TOKEN_EXPIRED,'status_code'=>StatusCode::TOKEN_EXPIRED));
            } else {

                //token转成管理员id
                $key        = isset($_GET['primaryKey'])?$_GET['primaryKey']:'adminId';
                $_GET[$key] = $result;

                //token转成管理员id
                $key        = isset($_GET['primaryKey'])?$_GET['primaryKey']:'managerId';
                $_GET[$key] = $result;

            }
        }
    }
    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data,$type='') {
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'JSONP':
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                $handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
                exit($handler.'('.json_encode($data).');');
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
        }
    }
    protected function getClassName($name,$layer='',$level=''){

        $layer  =   $layer? : C('DEFAULT_C_LAYER');
        $level  =   $level? : ($layer == C('DEFAULT_C_LAYER')?C('CONTROLLER_LEVEL'):1);
        $class  =   parse_res_name($name,$layer,$level);

        return $class;

    }
}
