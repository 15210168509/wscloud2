<?php
namespace Admin\Model;

/**
 * Class LoginModel
 * @package Home\Model
 */
class LoginModel extends ApiModel
{
    /**
    * 登陆验证
    * @param $account string 用户名
    * @param $pass  string  密码
    * @return object {res -> true/false, msg -> "", code -> ""} 登陆认证信息
    */
    public function login($account, $pass){

        $_res = new \stdClass();

        $res = $this->_login($account, $pass);

        if(!$res){
            $_res->res = false;
            $_res->msg = "用户名和密码不匹配";
            $_res->code = "no return";
        }
        else if(!$res['code']){
            $_res->res = false;
            $_res->msg = $res['msg'];
            $_res->code = "nok";
        }
        else{
            $_res->res = true;
            $_res->msg = "登陆成功";
            $_res->code = "ok";
            $_res->data = $res['data'];

        }
        if(C("Debug"))
            $_res->Debug = array("apiData" => $res, "paras" => array("account" => $account, "pass" => $pass));
        return $_res;
    }

    /**
    * 用户登录
    */
    public function _login($username, $password){
        return $this->getResult('/login/login/account/'.$username.'/password/'.$password.'/type/'.C('TOKEN_TYPE'));
    }

    /**
    * 检查查询密钥是否有效
    */
    public function check($accessToken){
        return $this->getResult("oauth2/checkAccessToken/".$accessToken, "get", null, array("base_url" => C("OAUTH_API_SERVER")));
    }

    /**
     * 用户退出登录
     */
    public function logOut($type)
    {
        return $this->getResult("/Login/logOut/type/".$type, "get");
    }

    /**
     * 发送验证码
     * @param $phone
     * @return string
     */
    public function sendMobileVerificationCode($phone)
    {
        return $this->getResult("/Admin/sendMobileVerificationCode/phone/$phone", "get");
    }

    /**
     * 验证手机验证码
     * @param $phone
     * @param $code
     * @param $label
     * @return string
     */
    public function ajaxValidateMobileVerificationCode($phone,$code,$label)
    {
        return $this->getResult("/Admin/ajaxValidateMobileVerificationCode/phone/$phone/code/$code/label/$label", "get");
    }

    /**
     * 公司注册
     * @param $data
     * @return string
     */
    public function registerCompany($data)
    {
        return $this->getResult('/Company/registerCompany','post',$data);
    }

}
?>