<?php
namespace Lib;
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/1/12
 * Time: 17:18
 *
 * 发送短信，验证码，验证码验证类
 */

class SmsService
{

    public static $error      = "";
    public static $errorCode  = "";

    /**
     * 发送验证码
     * @param int $phone 手机号码
     * @param int $label 短信模板类型
     * @return int
     */
    public static function sendVerificationCode($phone, $label)
    {
        $templateModel = D('Home/Sms');
        // 查询短信模板
        $data = $templateModel->where(array('type'=>$label, 'del_flg'=>0))->find();
        if (!$data) { // 短信模板不存在

            self::$error        = Msg::SMS_TEMPLET_ERROR;
            self::$errorCode    = StatusCode::SMS_TEMPLET_ERROR;

            return false;
        } else {

            $code    = Tools::generateCheckCode();
            $text    = $data['template'];
            $pattern = '/%s/';
            $text = preg_replace(array($pattern), array($code), $text, 1);

            if(!self::send($phone,$text)){//发送失败

                self::$error        = Msg::CODE_FAIL_SEND;
                self::$errorCode    = StatusCode::CODE_FAIL_SEND;

                return false;
            }else{ // 发送成功

                if(S($phone.'_'.$label, $code, 600)){ // 存储到session，时间10分钟
                    return true;
                } else {
                    self::$error        = Msg::CODE_FAIL_SAVE;
                    self::$errorCode    = StatusCode::CODE_FAIL_SAVE;
                    return false;
                }
            }
        }
    }

    /**
     * 验证验证码
     * @param int $phone 手机号码
     * @param int $verificationCode 待验证验证码
     * @param int $label 短信模板类型
     * @return boolean
     */
    public static function isVerificationCodeCorrect($phone, $verificationCode, $label)
    {


        if (Tools::isEmpty($phone) || Tools::isEmpty($verificationCode)) {
            return false;
        }
        $code = S($phone.'_'.$label);
        if($code == $verificationCode){
            return true;
        }
        return false;
    }

    /**
     * 发送消息
     * @param int $phone 手机号码
     * @param int $label 短信模板类型
     * @param array $contentList 待替换内容，顺序替换
     * @return int
     */
    public static function sendTemplateInfo($phone, $label, $contentList=array())
    {
        $templateModel = D('Home/Sms');
        // 查询短信模板
        $data = $templateModel->where(array('type'=>$label, 'del_flg'=>0))->find();
        if (!$data) { // 短信模板不存在

            self::$error        = Msg::SMS_TEMPLET_ERROR;
            self::$errorCode    = StatusCode::SMS_TEMPLET_ERROR;

            return false;

        } else {

            $text    = $data['template'];
            $pattern = '/%s/';
            if (!empty($contentList)) {
                foreach ($contentList as $value) {
                    $text = preg_replace(array($pattern), array($value), $text, 1);
                }
            }

            if (!self::send($phone,$text)) { // 发送失败
                self::$error        = Msg::CODE_FAIL_SEND;
                self::$errorCode    = StatusCode::CODE_FAIL_SEND;
                return false;
            } else { // 发送成功
                return true;
            }
        }
    }


    public static function send($phone,$text){
//        header("Content-Type: text/html; charset=UTF-8");

        // 判断手机号发送数量是否超出限制
        $isSendMax = self::checkSendMax($phone);
        if (!$isSendMax) {
            return false;
        }

        $flag = 0;
        $params='';
        //要post的数据
        $argv = array(
            'sn'=>'SDK-WSS-010-10373', ////替换成您自己的序列号
            'pwd'=>strtoupper(md5('SDK-WSS-010-10373'.'18d79e7]-87')), //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
            'mobile'=>$phone,//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
            'content'=>$text.'[华迅微视]',//iconv( "GB2312", "gb2312//IGNORE" ,'您好测试短信[XXX公司]'),//'您好测试,短信测试[签名]',//短信内容
            //'content'=>iconv( "GB2312", "gb2312//IGNORE" ,'您好测试短信[华讯金安]'),
            'ext'=>'',
            'stime'=>'',//定时时间 格式为2011-6-29 11:09:21
            'msgfmt'=>'',
            'rrid'=>''
        );
        //构造要post的字符串
        //echo $argv['content'];
        foreach ($argv as $key=>$value) {
            if ($flag!=0) {
                $params .= "&";
                $flag = 1;
            }
            $params.= $key."="; $params.= urlencode($value);// urlencode($value);
            $flag = 1;
        }
        $length = strlen($params);
        //创建socket连接
        $fp = fsockopen("sdk.entinfo.cn",8061,$errno,$errstr,10) or exit($errstr."--->".$errno);
        //构造post请求的头
        $header = "POST /webservice.asmx/mdsmssend HTTP/1.1\r\n";
        $header .= "Host:sdk.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        //添加post的字符串
        $header .= $params."\r\n";
        //发送post的数据
        //echo $header;
        //exit;
        fputs($fp,$header);
        $inheader = 1;
        while (!feof($fp)) {
            $line = fgets($fp,1024); //去除请求包的头只显示页面的返回数据
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                // echo $line;
            }
        }
        //<string xmlns="http://tempuri.org/">-5</string>
        $line=str_replace("<string xmlns=\"http://tempuri.org/\">","",$line);
        $line=str_replace("</string>","",$line);
        $result=explode("-",$line);
        // echo $line."-------------";
        if(count($result)>1){
            //echo '发送失败返回值为:'.$line.'。请查看webservice返回值对照表';
            //todo:日志记录
            return false;
        }
        else{
            return true;
        }

    }

    /**
     * 判断手机号发送数量是否超出限制
     * @param $phone
     * @return boolean
     */
    public static function checkSendMax($phone)
    {
        $key = 'sendSmsMax'.$phone;
        $redis = RedisData::getRedis();

        $thisTime    = time();
        $overdueTime = strtotime(date('Y-m-d', $thisTime) . '23:59:59');
        $existsTime  = $overdueTime - $thisTime;

        // 判断Redis中是否存在key
        $isExists = $redis->exists($key);
        if (!$isExists) {

            // 新手机号，设置Redis
            $redis->setex($key, $existsTime, 1);
            return true;
        } else {

            // 判断发送数量是否达到上限
            $num = $redis->get($key);
            if (false !== $num && $num < CommonConst::PHONE_SEND_MAX) {

                // 未超出限制
                $redis->incr($key);
                return true;
            } else {

                // 超出限制
                return false;
            }
        }
    }
}