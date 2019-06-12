<?php
namespace Lib;
/**
 * 工具类
 * @author wrf
 */
class Tools{

    /**
     * 生成表的主键id,long类型
     *
     * @author chengfanke
     */
    public static function generateId()
    {
        $time = microtime(true);
        $unique_id = str_shuffle(str_replace('.', '', $time));
        $arr = str_split($unique_id);
        shuffle($arr);
        while (count($arr) > 9) {
            $index = mt_rand(0, count($arr)-1);
            $arr   = array_values($arr);
            unset($arr[$index]);
        }
        array_unshift($arr, mt_rand(1, 9));
        $unique_id = implode('', $arr);
        return $unique_id;
    }

    /**
     * 生成md5字符串,用于存储密码
     * @param  string $str 明文
     * @return string MD5加密过的字符串
     */
    public static function generateMd5($str)
    {
        return md5($str);
    }

    /**
     * 验证码生成
     * @param int length int 生成长度
     *@return int 4位随机数字
    */
    public static function generateCheckCode($length = 4){
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }

  /**
   * 密码验证：只能为数字字母组合，长度6~20
   * @param $pwd string 验证的密码
   * @return boolean,如果不符合，返回false，反之，返回true
   * @auth wrf
  */
  public static function checkPwd($pwd){

      return preg_match('/^[_0-9a-z]{6,20}$/i',$pwd)?true:false;

  }

  /**
   * 手机号验证：长度11
   * @param $phone string 验证的手机号
   * @return boolean,如果不符合，返回false，反之，返回true
   * @author wrf
  */
  public static function checkPhone($phone){
      return preg_match("/^1[34578]{1}\d{9}$/",$phone);
  }

  /**
   * 检查是否为空
   * @param $val string 待检查字符串
   * @return boolean,如果为空，返回true，反之，返回true
  */
  public static function isEmpty($val)
  {
      return empty($val);
  }
  /**
  * 检查是否为NULL
  * @param $val string 待检查字符串
  * @return boolean,如果为NULL，返回true，反之，返回true
  */
  public static function isNull($val){
      return  NULL === $val;
  }

  /**
   * 检查是否为数字
   * @param string $val 带检查字符串
   * @return boolean 如果为数字，返回true，反之，返回true
  */
   public static function isNumeric($val){
       return is_numeric($val);
   }
   /**
    * 检查是否为大于1的浮点数和整数，小数点后两位
    * @param string $val 待检查字符串
    * @return boolean 如果为数字，返回true，反之，返回true
   */
    public static function isFloat($val){
       return preg_match('/^\d+(\.\d{1,2})?$/', $val);
    }

    /**
     * 验证姓名
     * @param $val
     * @return int
     */
    public static function isName($val){
        return preg_match('/^[\x{4e00}-\x{9fa5}]{1,24}$/u',$val);
    }

    /**
     * 验证长宽，小数点后一位
     * @param $val
     * @return int
     */
    public static function isWidth($val){
        return preg_match('/^[1-9]\d*([.][1-9])?$/', $val);
    }
}