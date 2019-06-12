<?php
namespace Lib;
use Lib\Mqtt\MsgPublish;
use Lib\Tcpdf\Tcpdf;
use Think\Model;
use Lib\RedisLock;

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
     * 生成编号
     * @param  int    $type 编号类型
     * @return string 生成的编号，未知类型的编号返回false
     * @author dbn
     * 规则:XXXyyyyMMddHHmmssNNNN
     *     XXX为三位大写字母标识编号类型,ORD代表订单编号,TRA代表运输单编号, CYC代表作业编号
     *     yyyyMMddHHmmss为代表当前时间年月日时分秒的数字
     *     NNNN为顺序数字,代表同一秒内生成的同类型订单的顺序
     *     如:ORD201701220952590003
     */
    public static function generateCode($type)
    {
        $is_code = S('codeNumber');
        if (false === $is_code || $is_code > 1000) {
            S('codeNumber', 0);
        }
        $number = S('codeNumber');
        switch ($type) {
            case self::CODE_TYPE_ORDER: // 订单编号
                $code = 'ORD' . date('YmdHis') . (++$number);
                S('codeNumber', $number);
                break;
            case self::CODE_TYPE_TRANSPORT: // 运输单编号
                $code = 'TRA' . date('YmdHis') . (++$number);
                S('codeNumber', $number);
                break;
            case self::CODE_TYPE_CYCLE: // 作业编号
                $code = 'CYC' . date('YmdHis') . (++$number);
                S('codeNumber', $number);
                break;
            case self::CODE_TYPE_LINE:
                $code = 'LIN' . date('YmdHis') . (++$number);
                S('codeNumber', $number);
                break;
            case self::CODE_TYPE_ORDER_CONTRACT: // 订单合同编号
                $code = 'HXORDCONT' . date('YmdHis') . (++$number);
                S('codeNumber', $number);
                break;
            default :
                return false;
                break;
        }
        return $code;
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
     * 随机密码生成
     * @param  int    $length 随机密码长度
     * @return string 随机密码
     */
    public static function generatePassword($length = 6){
        $chars='abcdefghijklmnopqrstuvwxy123456789';
        mt_srand((double)microtime()*1000000*getmypid());
        $password='';
        while(strlen($password) < $length)
            $password.=substr($chars,(mt_rand()%strlen($chars)),1);
        return $password;
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
      return preg_match("/^1[34578]{1}\d{9}$/",$phone) == 1;
    }

    /**
     * 验证邮箱是否正确
     * author 李文起
     * @param $email
     * @return bool
     */
    public static function checkEmail($email){
        return preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/",$email) == 1;
    }

    /**
     * 检测手机号和电话号都可用
     * author 李文起
     * @param $phone
     * @return bool
     */
    public static function checkPhoneAndTel($phone){
        return preg_match("/((\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$)/",$phone) == 1;
    }

    /**
     * 检查是否为整数
     * @param $number int 待验证参数
     * @return boolean
    */
    public static function checkNumber($number){
        return is_numeric($number);
    }
    /**
    * 检查金额
    * @param string $money 验证金额
    * @return boolean, 如果不符合，返回false，反之，返回true
    */
    public static function checkMoney($money){
       return preg_match('/^[1-9]\d*(.[0-9]{1,2})?$/', $money);
    }

    /**
     * @param $name   string    待检测姓名
     * @return int
     */
    public static function checkname($name)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]{1,24}$/u',$name);
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
     * 获取当前格式化后的日期时间和带毫秒数 YYYY-mm-dd HH:ii:ss,ms
     * @return string
     * @author dbn
     */
    public static function getTimeFormatting(){
        $mtime = explode(' ',microtime());
        $date = date('Y-m-d H:i:s', $mtime[1]);
        $ms = explode('.', $mtime[0]);
        $formatting = $date .','. $ms[1];
        return $formatting;
    }

    /**
     * CURL发送请求
     * @param  string  $url  请求地址
     * @param  array   $data 请求数据
     * @param  string  $type 请求类型
     * @param  boolean $ssl  是否是HTTPS请求
     * @return string  响应主体Content
     */
    public static function curlRequest($url, $data=array(), $type='get', $ssl=false)
    {
        // curl请求
        $curl = curl_init();
        // 设置curl选项
        if ($type == 'get' && count($data) > 0) {
            $param = '';
            $init = true;
            foreach ($data as $key => $val) {
                if ($init) {
                    $param .= '?'.$key.'='.$val;
                } else {
                    $param .= '&'.$key.'='.$val;
                }
                $init = false;
            }
            $url .= $param;
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true); // referer 请求来源
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时时间
        // SSL相关
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 是否在服务端进行验证
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '2'); // 检查服务器SSL证书中是否存在一个公用名
        }
        // POST请求
        if ($type == 'post') {
            curl_setopt($curl, CURLOPT_POST, true); // 是否为post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // 处理请求数据
        }
        // 处理响应结果
        curl_setopt($curl, CURLOPT_HEADER, false); // 是否处理响应头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 是否返回响应结果
        // 发送请求
        $response = curl_exec($curl);
        return $response;
    }

    /**
     * 简单更新表信息
     * @param  string $modelName  模型名
     * @param  array  $data       更新数据 array(field=>value,...)
     * @param  mixed  $where      更新条件
     * @return boolean
     * @author dbn
     */
    public static function updateTableInfo($modelName, $data, $where=array()) {
        $model = D('Home/'.$modelName);
        
        if (!$model->create($data, Model::MODEL_UPDATE, true)) {
            return false;
        } else {
            $res = $model->where($where)->save();

            if (false !== $res) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 简单添加一条信息
     * @param  string $modelName  模型名
     * @param  array  $data       更新数据 array(field=>value,...)
     * @return boolean
     * @author dbn
     */
    public static function addTableInfo($modelName, $data) {
        $model = D('Home/'.$modelName);

        if (!$model->create($data, Model::MODEL_UPDATE, true)) {
            return false;
        } else {
            $res = $model->add();

            if (false !== $res) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 删除表信息，物理删除
     * @param  string $modelName  模型名
     * @param  mixed  $where      删除条件
     * @return boolean
     * @author dbn
     */
    public static function delTableInfo($modelName, $where) {
        $model = D('Home/'.$modelName);
        $res = $model->where($where)->delete();
        if (false !== $res) return true;
        return false;
    }

    /**
     * 插入表信息，没有则插入，有则更新
     * @param string $modelName 模型名
     * @param array  $data      插入数据，插入数据中必须包含主键字段
     * @return boolean
     * @author dbn
     */
    public static function mysqlInsertExitsUpdate($modelName, $data)
    {
        $model = D('Home/'.$modelName);
        if (!array_key_exists($model->getPk(), $data)) return false;
        $data = $model->create($data, $model::MODEL_INSERT);
        $addSql = $model->fetchSql(true)->add($data);
        $strSql = '';
        foreach ($data as $key=>$val) {
            if ($key != $model->getPk()) $strSql .= empty($strSql) ? "`$key`='$val'" : ",`$key`='$val'";
        }
        $sql = $addSql . ' ON DUPLICATE KEY UPDATE ' . $strSql;
        $res = $model->execute($sql);
        if (false !== $res) { return true; } else { return false; }
    }

    /**
     * 乐观锁更新表信息
     * @param  string $modelName  模型名
     * @param  int    $pk         主键
     * @param  int    $updateTime 更新时间，排他键
     * @param  array  $data       更新数据 array(field=>value,...)
     * @param  array  $where      更新条件扩展
     * @param  int    $depth      递归深度
     * @return boolean
     * @author dbn
     */
    public static function mysqlCheckAndSet($modelName, $pk, $updateTime, $data, $where=array(), $depth=1)
    {
        if ($depth <= 5) {
            $model = D('Home/'.$modelName);
            if (is_object($model) && is_numeric($pk) && is_numeric($updateTime) && is_array($data) && is_array($where) && count($data) > 0) {
                if (!empty($where)) { $where['_logic'] = 'and'; $map['_complex'] = $where; }
                $map['id']           = array('EQ', $pk);
                $map['update_time']  = array('EQ', $updateTime);

                if (!$model->create($data, Model::MODEL_UPDATE, true)) {
                    return false;
                } else {
                    $res = $model->fetchSql(false)->where($map)->save(); // save方法的返回值是影响的记录数，如果返回false则表示更新出错，因此一定要用恒等来判断是否更新失败。

                    if (false !== $res && $res > 0) {
                        return true;
                    } else {
                        if (!empty($where)) { $where['_logic'] = 'and';$uMap['_complex'] = $where; }
                        $uMap['id'] = array('EQ', $pk);
                        $info       = $model->where($uMap)->find();
                        return self::mysqlCheckAndSet($modelName, $pk, $info['update_time'], $data, $where, $depth + 1);
                    }
                }
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * 对字符串进行加密
     * @param  string $str 需要加密的字符串
     * @param  string $key 加密密钥
     * @return string 加密后的串
     * @author dbn
     */
    public static function strEncrypt($str, $key)
    {
        $key = md5($key);
        $pkey = pack('H*', $key);
        $plaintext  = $str;
        $iv_size    = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv         = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $pkey, $plaintext, MCRYPT_MODE_CBC, $iv);
        $ciphertext = $iv . $ciphertext;
        $l = strlen($key);
        if ($l < 16) $key = str_repeat($key, ceil(16/$l));
        if ($m = strlen($ciphertext)%8) $ciphertext .= str_repeat("\x00",  8 - $m);
        $val = openssl_encrypt($ciphertext, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        return base64_encode($val);
    }

    /**
     * 对字符串进行解密
     * @param  string $str 加密的串
     * @param  string $key 解密密钥
     * @return string 解密后字符串
     * @author dbn
     */
    public static function strDecrypt($str, $key)
    {
        $key = md5($key);
        $ciphertext_dec = base64_decode($str);
        $l = strlen($key);
        if ($l < 16) $key = str_repeat($key, ceil(16/$l));
        $ciphertext_dec = openssl_decrypt($ciphertext_dec, 'BF-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING);
        $key     = pack('H*', $key);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv_dec  = substr($ciphertext_dec, 0, $iv_size);
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec));
    }
}