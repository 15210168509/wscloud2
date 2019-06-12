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
use Think\Behavior;
/**
 * 行为扩展：签名检测
 */
class MethodCheckBehavior extends Behavior{
    public function run(&$params) {

        //检查请求方式
        if(!preg_match('/^[A-Za-z](\/|\w)*$/',CONTROLLER_NAME)){ // 安全检测
            $this->ajaxReturn(array('code'=>-1,'msg'=>'invalide controller'));
        }else{
            //获取控制器完整类名
            $className = $this->getClassName(CONTROLLER_NAME);
            //获取类结构描述
            $classDes   = new \ReflectionClass($className);
            //采用注释方法获取访问权限
            $method = $classDes->getmethod(ACTION_NAME);

            if ($method->isPublic() && !$method->isStatic()) {
                if ($method->getNumberOfParameters()>0 && C('URL_PARAMS_BIND')) {
                    switch ($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $vars = array_merge($_GET, $_POST);
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars = $_GET;
                    }
                    //获取方法的所有参数
                    $params = $method->getParameters();
                    foreach ($params as $param) {
                        $name = $param->getName();
                        if (!$param->isDefaultValueAvailable()&&!isset($vars[$name])) {
                            $this->ajaxReturn(array('code'=>-1,'msg'=>'missging '.$name));
                        }
                    }
                }
            } else {
                //访问方法为私有
                $this->ajaxReturn(array('code'=>-1,'msg'=>'access to this method is not allowed'));
            }
            /*
            $doc = $method->getDocComment();
            //获取标准注释
            $parser = new DocParser();
            $docItems = $parser->parse($doc);
            //访问方式
            if(!isset($docItems['method'])){
                $this->ajaxReturn(array('code'=>-1,'msg'=>'missing document desc "method"'));
            }else{
                $requestMethod = $docItems['method'];

                if(strtolower($requestMethod) != strtolower(REQUEST_METHOD) ){
                    $this->ajaxReturn(array('code'=>-1,'msg'=>'invalid request method'.$requestMethod.' '.REQUEST_METHOD));
                }else{//检查参数

                }
                return ;//通过检查
            }*/

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
