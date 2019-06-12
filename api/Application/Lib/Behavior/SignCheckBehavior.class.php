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
use Lib\Sign;
use Think\Behavior;
/**
 * 行为扩展：签名检测
 */
class SignCheckBehavior extends Behavior{

    public function run(&$params)
    {
        if(C('API_STRICT_MODE')){
            //获取url签名
            if (!isset($_GET['SIGN']) || $_GET['SIGN']=='') {
                $this->ajaxReturn(array('code'=>-1,'msg'=>'signature missing'));
            } else {

                //获取当前请求uri
                $url = $_SERVER[C('URL_REQUEST_URI')];
                $sign = new Sign();
                switch(strtolower(REQUEST_METHOD)){
                    case 'get':
                    case 'delete':
                         $signature = $sign->get($url);
                         break;
                    case 'post':
                    case 'put':
                        $postData  = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
                        $postData = objectToArray($postData);
                        $signature = $sign->post($url,$postData);
                        break;
                    default:
                        $signature = '';
                        break;
                }
                if($signature !== $_GET['SIGN']){
                    $this->ajaxReturn(array('code'=>-2,'msg'=>'signature invalid'));
                }
            }
        }
        //通过签名验证
        return ;

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
