<?php

include_once 'Util/Autoloader.php';

/**
 * Class RestClient
 */
class RestClient
{
    private static $appKey    = "";
    private static $appSecret = "";
    private static $host      = "";
    private static $env       = 1; // API环境，1：测试，2：预发，3：发布
    private static $loggerFlg = false;
    private static $logger    = null;


    public function __construct($appKey, $appSecret, $host, $env, $loggerFlg, $logger)
    {
        $this::$appKey    = $appKey;
        $this::$appSecret = $appSecret;
        $this::$host      = $host;
        $this::$env       = $env;
        $this::$loggerFlg = $loggerFlg;
        $this::$logger    = $logger;
    }

    /**
     * GET请求
     * 服务端获取：$_GET
     */
    public function doGet($path, $host) {
        $host = empty($host) ? $this::$host : $host;

        $request = new HttpRequest($host, $path, HttpMethod::GET, $this::$appKey, $this::$appSecret);

        //设定Content-Type
        $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_TEXT);

        //设定Accept
        $request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_TEXT);

        //测试环境
        $this->setEnvironment($request);

        //指定参与签名的header
        $request->setSignHeader(SystemHeader::X_CA_TIMESTAMP);
        $response = HttpClient::execute($request);
        $this->logger($request, $response);
        return $this->setReturn($response);
    }

    /**
     * POST请求
     * 服务端获取：json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true)
     */
    public function doPost($path, $host, $data) {
        $host = empty($host) ? $this::$host : $host;

        $request = new HttpRequest($host, $path, HttpMethod::POST, $this::$appKey, $this::$appSecret);

        $bodyContent = json_encode($data);

        //设定Content-Type
        $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_JSON);

        //设定Accept
        $request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_JSON);

        //测试环境
        $this->setEnvironment($request);

        //注意：业务body部分，不能设置key值，只能有value
        if (0 < strlen($bodyContent)) {
            $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_MD5, base64_encode(md5($bodyContent, true)));
            $request->setBodyString($bodyContent);
        }

        //指定参与签名的header
        $request->setSignHeader(SystemHeader::X_CA_TIMESTAMP);

        $response = HttpClient::execute($request);
        $this->logger($request, $response);
        return $this->setReturn($response);
    }

    /**
     * PUT请求
     * 服务端获取：json_decode(file_get_contents('php://input'), true)
     */
    public function doPut($path, $host, $data) {
        $host = empty($host) ? $this::$host : $host;

        $request = new HttpRequest($host, $path, HttpMethod::PUT, $this::$appKey, $this::$appSecret);

        $bodyContent = json_encode($data);

        //设定Content-Type
        $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_TYPE, ContentType::CONTENT_TYPE_JSON);

        //设定Accept
        $request->setHeader(HttpHeader::HTTP_HEADER_ACCEPT, ContentType::CONTENT_TYPE_JSON);

        //测试环境
        $this->setEnvironment($request);

        //注意：业务body部分，不能设置key值，只能有value
        if (0 < strlen($bodyContent)) {
            $request->setHeader(HttpHeader::HTTP_HEADER_CONTENT_MD5, base64_encode(md5($bodyContent, true)));
            $request->setBodyString($bodyContent);
        }

        //指定参与签名的header
        $request->setSignHeader(SystemHeader::X_CA_TIMESTAMP);

        $response = HttpClient::execute($request);
        $this->logger($request, $response);
        return $this->setReturn($response);
    }

    /**
     * 设置返回结果
     */
    private function setReturn(HttpResponse $response)
    {
        return array(
            'httpCode'     => $response->getHttpStatusCode(),
            'resultData'   => $response->getBody(),
            'errorMessage' => $response->getErrorMessage(),
        );
    }

    /**
     * 设置API环境
     */
    private function setEnvironment(HttpRequest $request)
    {
        switch ($this::$env) {
            case 2:
                $request->setHeader(SystemHeader::X_CA_STAG, "PRE");
                break;
            case 3:
                $request->setHeader(SystemHeader::X_CA_STAG, "RELEASE");
                break;
            case 1:
            default:
                $request->setHeader(SystemHeader::X_CA_STAG, "TEST");
                break;
        }
    }

    /**
     * 记录请求日志
     */
    private function logger(HttpRequest $request, HttpResponse $response)
    {
        if ($this::$loggerFlg && method_exists($this::$logger, 'info')) {
            $reflectionMethod = new ReflectionMethod($this::$logger, 'info');

            $data = json_encode(array(
                'request_host'          => $request->getHost(),
                'request_path'          => $request->getPath(),
                'request_method'        => $request->getMethod(),
                'request_bodys'         => $request->getBodys(),
                'response_code'         => $response->getHttpStatusCode(),
                'response_body'         => $response->getBody(),
                'response_errorMessage' => $response->getErrorMessage()
            ));

            if ($reflectionMethod->isStatic()) {
                $reflectionMethod->invokeArgs(null, array(
                    'rest client',
                    $data
                ));
            } else {
                $reflectionMethod->invokeArgs($this::$logger, array(
                    'rest client',
                    $data
                ));
            }
        }
    }
}