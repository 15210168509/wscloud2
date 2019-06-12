<?php
namespace TCT;
require_once "WxConfig.php";

class WxApi{
    
	/**
	 * 
	 * 通过跳转获取用户的openid，跳转流程如下：
	 * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
	 * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
	 * 
	 * @return 用户的openid
	 */
    private $mmc;

    public function __construct() {
        
        //$this->mmc= memcache_init();
         
    }
    
	public function GetOpenid()
	{
		//通过code获得openid
		if (!isset($_GET['code'])){
			//触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].($_SERVER['QUERY_STRING']==''?'':'?'.$_SERVER['QUERY_STRING']));
			$url = $this->__CreateOauthUrlForCode($baseUrl);
            //die($url);
			Header("Location: $url");
            //echo ("http-equiv='refresh' content=10;URl='".$url."'");
			//exit();
		} else {
			//获取code码，以获取openid
		    $code = $_GET['code'];
			$info = $this->getAuthInfoFromMp($code);
            //var_dump($info);
			return $info["openid"];
		}
	}
    /**
     *
     *获取全局Access_Token
     *
     */
    
    public function getGlobalAccessToken(){
        
        //全局access_token,缓存
        $data = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wx/'."access_token.json"));

         if ($data->expire_time < time()){

        	$url = $this->__CreateOauthUrlForGlobalAccessToken();

             $res = json_decode($this->__HttpGet($url));
             $access_token = $res->access_token;
             if ($access_token) {
                 $data->expire_time = time() + 7000;
                 $data->access_token = $access_token;
                 $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/wx/'."access_token.json", "w");
                 fwrite($fp, json_encode($data));
                 fclose($fp);
             }
            
        }else{
             $access_token = $data->access_token;
            
        }


        return $access_token;
    }
   	/**
	 * 
	 * 通过code从工作平台获取openid机器access_token
	 * @param string $code 微信跳转回来带上的code
	 * 
	 * @return array(
     *  "access_token"=>"ACCESS_TOKEN",
       "expires_in"=>7200,
       "refresh_token"=>"REFRESH_TOKEN",
       "openid"=>"OPENID",
       "scope"=>"SCOPE",
       "unionid"=> "o6_bmasdasdsad6_2sgVt7hMZOPfL"
       )
	 */
	public function getAuthInfoFromMp($code)
	{ 
        
        $url = $this->__CreateOauthUrlForOpenid($code);
        //初始化curl
        $res = $this->__HttpGet($url); 
        
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        
		return $data;
	}
    /**
     *
     *通过网页授权的Access_Token获取用户信息
     *@return 
           array(
           "openid"=>" OPENID",
           " nickname"=> NICKNAME,
           "sex"=>"1",
           "province"=>"PROVINCE"
           "city"=>"CITY",
           "country"=>"COUNTRY",
            "headimgurl"=>"http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46", 
            "privilege"=>[
            "PRIVILEGE1"
            "PRIVILEGE2"
            ],
            "unionid"=>"o6_bmasdasdsad6_2sgVt7hMZOPfL"
        )
     */ 
    
    public function getUserInfoByOauthAccessToken(){
         
    	//通过code获得openid
		if (!isset($_GET['code'])){
			
            //触发微信返回code码
			$baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].$_SERVER['QUERY_STRING']);
			$url = $this->__CreateOauthUrlForCode($baseUrl);
			Header("Location: $url");
			exit();
		
        } else {
			//获取code码，以获取openid
		    $code = $_GET['code'];
            $info = $this->getAuthInfoFromMp($code); 
             
           
            $url = $this->__CreateOauthUrlForUserinfo($info["access_token"],$info["openid"]); 
             
             //初始化curl 
            $res = $this->__HttpGet($url); 
            
            //返回用户信息
            $data = json_decode($res,true);
            $this->data = $data; 
            
            return $data;
        }
    }
    /*
     *通过全局Access_token 获取用户信息
     *
     *@return array(
     	 "subscribe"=>1,
        "openid"=>"oLVPpjqs2BhvzwPj5A-vTYAX4GLc",
        "nickname"=>"刺猬宝宝",
        "sex"=>1,
        "language"=>"zh_CN",
        "city"=>"深圳",
        "province"=>"广东",
        "country"=>"中国",
        "headimgurl": "http://wx.qlogo.cn/mmopen/JcDicrZBlREhnNXZRudod9PmibRkIs5K2f1tUQ7lFjC63pYHaXGxNDgMzjGDEuvzYZbFOqtUXaxSdoZG6iane5ko9H30krIbzGv/0",
        "subscribe_time"=>1386160805
     )
     */
    public function getUserInfoByGloabalAccessToken(){
    
    	$openid = $this->GetOpenid();

        $access_token = $this->getGlobalAccessToken();

        $url = $this->__CreateGlobaUrlForUserinfo($access_token,$openid);
        
        $res = $this->__HttpGet($url);
        
        return json_decode($res);
        
    }
    
   /**
	 * 
	 * 构造获取code的url连接
	 * @param string $redirectUrl 微信服务器回跳的url，需要url编码
	 * 
	 * @return 返回构造好的url
	 */
	private function __CreateOauthUrlForCode($redirectUrl)
	{
		$urlObj["appid"] = WxConfig::APPID;
		$urlObj["redirect_uri"] = "$redirectUrl";
		$urlObj["response_type"] = "code";
		$urlObj["scope"] = "snsapi_base";
		$urlObj["state"] = "STATE"."#wechat_redirect";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
	}
    /**
	 * 
	 * 拼接签名字符串
	 * @param array $urlObj
	 * 
	 * @return 返回已经拼接好的字符串
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = "";
		foreach ($urlObj as $k => $v)
		{
			if($k != "sign"){
				$buff .= $k . "=" . $v . "&";
			}
		}
		
		$buff = trim($buff, "&");
		return $buff;
	}
    /**
	 * 
	 * 构造获取open和access_toke的url地址
	 * @param string $code，微信跳转带回的code
	 * 
	 * @return 请求的url
	 */
	private function __CreateOauthUrlForOpenid($code)
	{
		$urlObj["appid"] = WxConfig::APPID;
		$urlObj["secret"] = WxConfig::APPSECRET;
		$urlObj["code"] = $code;
		$urlObj["grant_type"] = "authorization_code";
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
	}
    /**
	 * 
	 * 构造获取网页授权Oatuth2.0，用户信息url地址
	 * @param string $access_token，微信授权认证access_token. string openid 用户微信唯一凭证
	 * 
	 * @return 请求的url
	 */
    private function __CreateOauthUrlForUserinfo($access_token,$openid){
        
        
    	$urlObj["access_token"] = $access_token;
		$urlObj["openid"] = $openid;
		$urlObj["lang"] = "zh_CN"; 
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/sns/userinfo?".$bizString;
    	
    }
    /**
     *构造通用获取用户信息url地址
     *@param string $access_token,全局Access_Token
     *@param string $openid,用户微信唯一凭证
     *
     *@return 请求的url
     */
    private function __CreateGlobaUrlForUserinfo($access_token,$openid){
    	$urlObj["access_token"] = $access_token;
		$urlObj["openid"] = $openid;
		$urlObj["lang"] = "zh_CN"; 
		$bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/cgi-bin/user/info?".$bizString;
    }
    
    /**
	 * 
	 * 构造获取全局Access_Token的url地址 
	 * 
	 * @return 请求的url
	 */
    private function __CreateOauthUrlForGlobalAccessToken(){
        
    	$urlObj["appid"] = WxConfig::APPID;
		$urlObj["secret"] = WxConfig::APPSECRET;
        $urlObj["grant_type"]="client_credential";
        $bizString = $this->ToUrlParams($urlObj);
		return "https://api.weixin.qq.com/cgi-bin/token?".$bizString;
    
    }
    
    
    /**
	 * 
	 * 模拟Http Get请求
	 * @param string $url，get请求的地址
	 * 
	 * @return 请求url返回的结果
	 */
    private function __HttpGet($url){
        
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
?>