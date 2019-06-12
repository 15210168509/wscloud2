<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-12-26
 * Time: 下午4:26
 */

namespace Lib;

use Think\Log;
use Think\Model;
use TCT\RestClient;
use TCT\Jobject;

/**
 * 数据操作接口类
 */
class ApiModel{

    protected $__state_set = null;

    public $context;

    protected $state;

    public function __construct($config = array())
    {
        $this->context = Factory::getContext();
        // Set the model state
        if (array_key_exists('state', $config))
        {
            $this->state = $config['state'];
        }
        else
        {
            $this->state = new JObject;
        }
    }
    public function getName(){
        $className = get_class($this);
        return $className;
    }
    function httpGet($url) {
        $curl = curl_init(); // 初始化CURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 将获取到的数据进行返回
        curl_setopt($curl, CURLOPT_TIMEOUT, 500); // 设置最大执行时间
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过安全验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 跳过安全验证
        curl_setopt($curl, CURLOPT_URL, $url); // 设置请求的地址
        $res = curl_exec($curl); // 执行请求
        curl_close($curl); // 关闭
        return $res; // 返回结果
    }

    /**
     * 数据处理
     * @param string $url 请求的地址
     * @param string $method 请求的类型
     * @param array $data 数据
     * @param array $parameters 域名地址
     * @return string
     */
    public function getResult($url,$method="get",$data=array(),$parameters=array()){

        $config = array("base_url" => C('API_SERVER')); // 获取配置中定义的API地址
        if(is_array($parameters)&&!empty( $parameters)){ // 判断是否有新的API接口地址如果有覆盖使用新的域名地址进行请求
            $config = array_merge($config,$parameters);
        }

        $api = new RestClient($config); // 此时的$config = ["base-url" => "http://api.office:81/Home"];
        switch($method){
            case "update":
                $result = $api->update($url,$data);
                break;
            case "put":
                $result = $api->put($url,$data);
                break;
            case "post":
                if (!empty($this->context->loginuser->company_id)) {
                    $data = $this->setPostCompany($data);
                }
                $result = $api->post($url, $data, array("Expect"=>""));
                break;
            case "delete":
                $result = $api->delete($url, $data, array("Expect"=>""));
                break;
            case "get":
            default:
                if (!empty($this->context->loginuser->company_id) && strpos($url, 'companyId') === false) {
                    $url = $url . '/companyId/' . $this->context->loginuser->company_id;
                }
                $result = $api->get($url,$data);
                break;
        }

        if(C("Debug"))
            return $result->response;

        if($result->info->http_code == 200){ // 获取请求状态
            //返回json格式数据
            return $result->response; //返回请求的状态josn数据
        }
        else{
            switch($result->info->http_code){
                case "403":
                    $msg = "403 forbidden";
                    break;
                case "404":
                    $msg = "404 not found";
                    break;
                default:
                    $msg = str_replace("\"","'", json_encode($data));
                    break;
            }
            return "{\"status\":\"".$result->info->http_code."\",\"msg\":\"".$msg.$result->response->headers->info."\"}";
        }
    }

    private function setPostCompany($data)
    {
        $isAllArray = true;
        foreach ($data as $val) {
            if (!is_array($val)) {
                $isAllArray = false;
                break;
            }
        }
        if ($isAllArray) {
            foreach ($data as &$val) {
                if (!isset($val['companyId'])) {
                    $val['companyId'] = $this->context->loginuser->company_id;
                }
            }
        } else {
            if (!isset($data['companyId'])) {
                $data['companyId'] = $this->context->loginuser->company_id;
            }
        }
        return $data;
    }
}