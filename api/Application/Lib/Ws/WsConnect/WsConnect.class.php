<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/15
 * Time: 11:16
 */

namespace Lib\Ws\WsConnect;

use Lib\Code;
use Lib\CommonConst;
use Lib\Logger\Logger;
use Lib\RedisLock;
use Lib\Ws\WsConfig;

class WsConnect
{
    private $_apiGateway = null;                // api网关
    private static $_maxRefreshToken    = 2;    // 单次请求，Token过期，最大刷取新Token次数
    private static $_maxNewTokenRequest = 1;    // 刷取新Token后，重新请求最大尝试次数，防止无限递归请求

    const API_GATEWAY_KEY    = '24859091';   //appkey
    const API_GATEWAY_SECRET = '46e65e3bafa12d58ea9f113b02410e95';//appsecret
    const API_GATEWAY_ENV    = 1;             //API环境，1：测试，2：预发，3：发布
    const API_LOGGER          = true;            //API接口请求日志


    public function __construct($restMethod = 'RestClient')
    {
        if ($restMethod == 'RestClient') {
            vendor('ApiGateway.RestClient');
            $this->_apiGateway = new \RestClient(
                self::API_GATEWAY_KEY,
                self::API_GATEWAY_SECRET,
                WsConfig::API_SERVER,
                self::API_GATEWAY_ENV,
                self::API_LOGGER,
                Logger::getInstance()
            );
        } else {
            vendor('ApiGateway.RestSyncClient');
            $this->_apiGateway = new \RestSyncClient(
                self::API_GATEWAY_KEY,
                self::API_GATEWAY_SECRET,
                WsConfig::API_SERVER,
                self::API_GATEWAY_ENV,
                self::API_LOGGER,
                Logger::getInstance()
            );
        }


        //获取access_token
        if (empty(RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID))) {
            $this->getAccessToken();
        }

    }

    /**
     * 数据处理
     * @param  string $path 请求的地址
     * @param  string $method 请求的类型 get|post|put
     * @param  array  $data 请求数据
     * @param  string $host 域名地址，默认使用配置文件API域名，协议(http或https)://域名:端口，注意必须有http://或https://
     * @return string
     */
    public function getResult($path, $method="get", $data=array(), $host='')
    {
        $apiService = $this->checkPath($path);
        $tokenPath  = $this->setPathToken($apiService);

        switch($method){
            case "post":
                $result = $this->_apiGateway->doPost($tokenPath, $host, $data);
                break;
            case "put":
                $result = $this->_apiGateway->doPut($tokenPath, $host, $data);
                break;
            case "get":
            default:
                $result = $this->_apiGateway->doGet($tokenPath, $host);
                break;
        }

        if (isset($result['httpCode']) && $result['httpCode'] >= 200 && $result['httpCode'] < 300) {

            $resultData = json_decode($result['resultData'], true);

            // 判断是否需要刷新token
            if ($resultData && isset($resultData['status_code']) && $resultData['status_code'] == WsConst::STATUS_TOKEN_EXPIRED) {

                if (self::$_maxNewTokenRequest > 0 && $this->refreshToken()) {

                    // 刷新成功，重新请求接口
                    self::$_maxNewTokenRequest--;
                    return $this->getResult($path, $method, $data, $host);
                }

                // 刷新不成功，清除用户信息 session 信息，需要用户重新登录，返回 302
                return $this->setResult(WsConst::CODE_ERROR, WsConst::STATUS_TOKEN_REFRESH_ERROR, WsConst::MSG_TOKEN_REFRESH_ERROR, '', '302', C('baseUrl').'/Login/loginPage');
            }

            // 判断是否已在其他设备登录
            if ($resultData && isset($resultData['status_code']) && $resultData['status_code'] == WsConst::STATUS_OTHER_DEVICE_LOGIN) {

                // 已在其他设备登录，清除用户信息 session 信息，需要用户重新登录，返回 302

                return $this->setResult(WsConst::CODE_ERROR, WsConst::STATUS_OTHER_DEVICE_LOGIN, WsConst::MSG_OTHER_DEVICE_LOGIN, '', '302', C('baseUrl').'/Login/loginPage');
            }

            // 请求成功
            return $resultData;
        }

        // 请求服务器失败
        return $this->setResult(WsConst::CODE_ERROR, WsConst::STATUS_HTTP_ERROR, WsConst::MSG_HTTP_ERROR, '', $result['httpCode'], $result['errorMessage']);
    }

    /**
     * 设置Token
     * @param string $path 请求地址
     * @return string
     */
    private function setPathToken($path)
    {
        $path = rtrim($path, '/');
        if (strpos($path, '/token/') === false) {
            if ( RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID)) {
                $path = $path . '/token/' .  RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID);
            } else {
                $path = $path . '/token/null';
            }
        }
        return $path;
    }

    /**
     * 统一网关控制器设置
     * @param $path
     * @return string
     */
    private function checkPath($path)
    {
        if (WsConfig::ENV != 'dev') {
            $prefix =WsConfig::SERVICE_PREFIX;
            // 统一替换
            $pathArray = explode('/',$path);
            $pathArray[1] = $prefix;
            $path = implode('/',$pathArray);
        }
        return $path;
    }

    /**
     * 刷新Token
     */
    private function refreshToken()
    {
        // 1. 判断用户 token 和 refresh_token 是否存在
        if ( RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID) &&  RedisLock::getInstance()->get('ws_refresh_token_'.WsConfig::APP_ID)) {

            // 2. 判断刷新次数是否超过限制
            if (self::$_maxRefreshToken > 0) {

                // 3. 刷新token
                $path = '';
                if (WsConfig::ENV == 'release'){
                    $path = '/Login/refreshToken/refreshToken/'. RedisLock::getInstance()->get('ws_refresh_token_'.WsConfig::APP_ID).'/token/'. RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID);
                } else {
                    $path = '/Login/refreshToken/refreshToken/'. RedisLock::getInstance()->get('ws_refresh_token_'.WsConfig::APP_ID).'/token/'. RedisLock::getInstance()->get('ws_token_'.WsConfig::APP_ID).'/type/'.CommonConst::THIRD_COMPANY_APP_ID_SECRET;
                }

                $path =  $this->checkPath($path);
                $result = $this->_apiGateway->doGet($path, WsConfig::API_SERVER);
                // 4. 验证请求返回
                if (isset($result['httpCode']) && $result['httpCode'] >= 200 && $result['httpCode'] < 300) {
                    $resultData = json_decode($result['resultData'], true);
                    if ($resultData && $resultData['code'] == WsConst::CODE_OK) {
                        // 5. 更新 session token , 上下文不会立即生效，更新当前 token
                         RedisLock::getInstance()->set('ws_token_'.WsConfig::APP_ID,$resultData['data']['token'],0);
                        return true;
                    }
                }
                // 5. 请求服务器失败，尝试重新刷取
                self::$_maxRefreshToken--;
                return $this->refreshToken();
            }
        }
        return false;
    }

    /**
     * 设置返回值
     */
    private function setResult($code, $statusCode, $msg, $data, $httpCode, $httpMsg)
    {

        //如果刷新token失败则重新获取token
        if ($statusCode == WsConst::STATUS_TOKEN_REFRESH_ERROR) {
            $this->getAccessToken();
        }

        return array(
            'code'        => $code,
            'status_code' => $statusCode,
            'msg'         => $msg,
            'data'        => $data,
            'http_code'   => $httpCode,
            'http_msg'    => $httpMsg
        );
    }

    /**
     * 获取access_token
     * author 李文起
     */
    public function getAccessToken(){

        $data['appId']              = WsConfig::APP_ID;
        $data['secret']             = WsConfig::SECRET;


        $res = $this->getResult('/OpenService/getAccessToken','post',$data);

        if ($res['code'] == Code::OK){
            RedisLock::getInstance()->set('ws_token_'.WsConfig::APP_ID,$res['data']['token'],0);
            RedisLock::getInstance()->set('ws_refresh_token_'.WsConfig::APP_ID,$res['data']['token'],0);
        }

        return $res;
    }
}