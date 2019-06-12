<?php
/**
 * Created by dbn.
 * Date: 18-04-19
 * Time: 14:26
 */

namespace Office\Model;
use Lib\Code;
use Lib\Factory;
use Lib\Logger\Logger;
use Lib\Msg;
use Lib\StatusCode;


/**
 * 数据操作接口类
 */
class ApiModel{

    private $_apiGateway = null; // api网关
    private $_context    = null; // 当前上下文
    private static $_maxRefreshToken    = 2; // 单次请求，Token过期，最大刷取新Token次数
    private static $_maxNewTokenRequest = 1; // 刷取新Token后，重新请求最大尝试次数，防止无限递归请求

    public function __construct()
    {
        $this->_context   = Factory::getContext();
        vendor('ApiGateway.RestClient');
        $this->_apiGateway = new \RestClient(
            C('API_GATEWAY_KEY'),
            C('API_GATEWAY_SECRET'),
            C('API_SERVER'),
            C('API_GATEWAY_ENV'),
            C('API_LOGGER'),
            Logger::getInstance()
        );
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
            if ($resultData && isset($resultData['status_code']) && $resultData['status_code'] == StatusCode::TOKEN_EXPIRED) {

                if (self::$_maxNewTokenRequest > 0 && $this->refreshToken()) {

                    // 刷新成功，重新请求接口
                    self::$_maxNewTokenRequest--;
                    return $this->getResult($path, $method, $data, $host);
                }

                // 刷新不成功，清除用户信息 session 信息，需要用户重新登录，返回 302
                $this->_context->clearSession();
                return $this->setResult(Code::ERROR, StatusCode::TOKEN_REFRESH_ERROR, Msg::TOKEN_REFRESH_ERROR, '', '302', C('baseUrl').'/Login/loginPage');
            }

            // 判断是否已在其他设备登录
            if ($resultData && isset($resultData['status_code']) && $resultData['status_code'] == StatusCode::OTHER_DEVICE_LOGIN) {

                // 已在其他设备登录，清除用户信息 session 信息，需要用户重新登录，返回 302
                $this->_context->clearSession();
                return $this->setResult(Code::ERROR, StatusCode::OTHER_DEVICE_LOGIN, Msg::OTHER_DEVICE_LOGIN, '', '302', C('baseUrl').'/Login/loginPage');
            }

            // 请求成功
            return $resultData;
        }

        // 请求服务器失败
        return $this->setResult(Code::ERROR, StatusCode::HTTP_ERROR, Msg::HTTP_ERROR, '', $result['httpCode'], $result['errorMessage']);
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
            if ($this->_context->loginuser->token) {
                $path = $path . '/token/' . $this->_context->loginuser->token;
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
        switch (strtolower(C('ENV'))) {
            case 'release': // 发布模式
                if (!is_null(C('ServicePrefix'))) {
                    $prefix       = C('ServicePrefix');
                    $pathArray    = explode('/',$path);
                    $pathArray[1] = $prefix;
                    $path         = implode('/',$pathArray);
                }
                break;

            case 'dev': // 开发模式

            default:

                break;
        }

        return $path;
    }

    /**
     * 刷新Token
     */
    private function refreshToken()
    {
        // 1. 判断用户 token 和 refresh_token 是否存在
        if ($this->_context->loginuser->token && $this->_context->loginuser->refresh_token) {

            // 2. 判断刷新次数是否超过限制
            if (self::$_maxRefreshToken > 0) {

                // 3. 刷新token
                $path = '/Login/refreshToken/refreshToken/'.$this->_context->loginuser->refresh_token.'/token/'.$this->_context->loginuser->token."/type/".C('TOKEN_TYPE');
                $path =  $this->checkPath($path);
                $result = $this->_apiGateway->doGet($path, C('API_SERVER'));

                // 4. 验证请求返回
                if (isset($result['httpCode']) && $result['httpCode'] >= 200 && $result['httpCode'] < 300) {
                    $resultData = json_decode($result['resultData'], true);
                    if ($resultData && $resultData['code'] == Code::OK) {
                        // 5. 刷新 token 成功，更新 session token
                        $this->_context->addKey('token', $resultData['data']['token']);
                        // 6. 更新 session token , 上下文不会立即生效，更新当前 token
                        $this->_context->loginuser->token = $resultData['data']['token'];
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
        return array(
            'code'        => $code,
            'status_code' => $statusCode,
            'msg'         => $msg,
            'data'        => $data,
            'http_code'   => $httpCode,
            'http_msg'    => $httpMsg
        );
    }

}