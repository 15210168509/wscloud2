<?php
namespace Lib;

class Jssdk {
  private $appId;
  private $appSecret;
  private $mmc;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }

  public function getSignPackage() {
      
    
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
      
    return $signPackage; 
  }
   
  public function getUserInfo($openID=''){
  	$accessToken = $this->getAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openID&lang=zh_CN";
    $res = json_decode($this->httpGet($url));
  	
      return $res;
  }

  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  public function getJsApiTicket() {

      $httpClient = new HttpClient(C('WX_API_SERVER'));
      $res = json_decode($httpClient->request(HttpClient::HTTP_GET,array('action'=>'jsticket','source'=>'1')));
      if ($res->code ==1){
         return $res->data;
      }
      return '';
  }

  public function getAccessToken(){
     
       
      $token_time = memcache_get($this->mmc,"token_time_".$this->appId);
       
      
       
      if(!$token_time||$token_time<time()){ 
          
           
          $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
          $res = json_decode($this->httpGet($url));
          
          
          $access_token = $res->access_token;
          
           
      	if($access_token){
          
            $expire_time = time() + 7000;
            memcache_set($this->mmc,"token_time_".$this->appId,$expire_time);
            
            memcache_set($this->mmc,"token_".$this->appId,$access_token);
      	}
      }else {
      	$access_token = memcache_get($this->mmc,"token_".$this->appId);
         
      }
      
    return $access_token;
  }
  
    public function getUserAccessToken($code){
      
    
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$code&grant_type=authorization_code";
    $res = json_decode($this->httpGet($url));
  	
      return $res;
    	
    }
    

  private function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
}
