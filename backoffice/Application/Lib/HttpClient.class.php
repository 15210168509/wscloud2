<?php
namespace Lib;
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/8/16
 * Time: 13:40
 */
class HttpClient{
    const HTTP_POST = 'post';
    const HTTP_GET  = 'get';
    private $url;
    private $method;

    public function __construct($url)
    {
        $this->url = $url;
    }
    public function request($method,$para = array(),$uri = null){

        $baseUrl = $uri==null? $this->url:$uri;
        if ($method== HttpClient::HTTP_GET) {

            if(count($para)>0){

                if (strpos($baseUrl,'?') === false) {
                    $baseUrl .='?';
                }
                foreach ($para as $key=>$value) {
                    $baseUrl .= $key.'='.$value.'&';
                }
                $baseUrl = substr($baseUrl,0,strlen($baseUrl)-1);
            }
            return $this->httpGet($baseUrl);
        } else {

            return $this->httpPost($baseUrl,$para);
        }
    }
    private function httpGet($uri) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $uri);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
    private function httpPost($uri,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}