<?php

namespace Lib;

/**
 * 数据验证
 * User: dbn
 * Date: 2017/10/18
 * Time: 11:04
 * =================================================================
 * /////验证规则/////
 * 验证规则，定义格式为：
 * array(
 *     array(验证字段1,验证规则,错误提示,[验证条件,附加规则]),
 *     array(验证字段2,验证规则,错误提示,[验证条件,附加规则]),
 *     ......
 * );
 *
 * /////说明/////
 * 验证字段 （必须）
 * 需要验证的字段名称，例如确认密码和验证码等等。有个别验证规则和字段无关的情况下，验证字段是可以随意设置的，例如expire有效期规则是和表单字段无关的。
 *
 * 验证规则 （必须）
 * 要进行验证的规则，需要结合附加规则，如果在使用正则验证的附加规则情况下，内置了一些常用正则验证的规则，可以直接作为验证规则使用。
 *
 * 提示信息 （必须）
 * 用于验证失败后的提示信息定义
 *
 * 验证条件 （可选）
 * 包含下面几种情况：
 * self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
 * self::MUST_VALIDATE   或者1 必须验证
 * self::VALUE_VALIDATE  或者2 值不为空的时候验证
 *
 * 附加规则 （可选）
 * 配合验证规则使用，包括下面一些规则：
 * --规则-------------------说明---------------------------------------------------------------------------------------
 * regex	   | 正则验证，定义的验证规则是一个正则表达式（默认）
 * callback    | 方法验证，定义的验证规则是一个数组array(class类, function类中方法)
 * confirm     | 验证表单中的两个字段是否相同，定义的验证规则是一个字段名
 * equal       | 验证是否等于某个值，该值由前面的验证规则定义
 * notequal    | 验证是否不等于某个值，该值由前面的验证规则定义
 * in          | 验证是否在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
 * notin       | 验证是否不在某个范围内，定义的验证规则可以是一个数组或者逗号分割的字符串
 * length      | 验证长度，定义的验证规则可以是一个数字（表示固定长度）或者数字范围（例如3,12 表示长度从3到12的范围）
 * between     | 验证范围，定义的验证规则表示范围，可以使用字符串或者数组，例如1,31或者array(1,31)
 * notbetween  | 验证不在某个范围，定义的验证规则表示范围，可以使用字符串或者数组
 * expire      | 验证是否在有效期，定义的验证规则表示时间范围，可以到时间，例如可以使用 2012-1-15,2013-1-15 表示当前提交有效期在2012-1-15到2013-1-15之间，也可以使用时间戳定义
 * ip_allow    | 验证IP是否允许，定义的验证规则表示允许的IP地址列表，用逗号分隔，例如201.12.2.5,201.12.2.6
 * ip_deny     | 验证IP是否禁止，定义的验证规则表示禁止的ip地址列表，用逗号分隔，例如201.12.2.5,201.12.2.6
 * ------------------------------------------------------------------------------------------------------------------
 */

class AutoValidation
{
    const MUST_VALIDATE         =   1;      // 必须验证
    const EXISTS_VALIDATE       =   0;      // 存在字段则验证
    const VALUE_VALIDATE        =   2;      // 值不为空则验证

    private   $_error           = array();  // 错误信息
    private   $_validate        = array();  // 验证规则
    private   $_patchValidate   = false;     // 是否批处理验证

    public function getError()
    {
        return $this->_error;
    }

    public function __construct($validate, $patchValidate = true)
    {
        $this->_validate      = $validate;
        $this->_patchValidate = $patchValidate;
    }

    /**
     * 数据验证
     * @param  array  $data 数据
     * @return boolean
     */
    public function validation($data)
    {
        if (!empty($this->_validate)) { // 如果设置了数据自动验证则进行数据验证

            $validate   =   $this->_validate;

            if($this->_patchValidate) { // 重置验证错误信息
                $this->_error = array();
            }

            foreach ($validate as $key => $val) {

                // 验证因子定义格式
                if(0 == strpos($val[2],'{%') && strpos($val[2],'}'))
                    // 支持提示信息的多语言 使用 {%语言定义} 方式
                    $val[2]  =  $this->L(substr($val[2], 2, -1));
                    $val[3]  =  isset($val[3]) ? $val[3]: self::EXISTS_VALIDATE;
                    $val[4]  =  isset($val[4]) ? $val[4]: 'regex';

                // 判断验证条件
                switch ($val[3]) {
                    case self::MUST_VALIDATE:     // 必须验证 不管表单是否有设置该字段
                        if(false === $this->validationField($data, $val))
                            return false;
                        break;
                    case self::VALUE_VALIDATE:    // 值不为空的时候才验证
                        if('' != trim($data[$val[0]]))
                            if(false === $this->validationField($data, $val))
                                return false;
                        break;
                    default:   // 默认表单存在该字段就验证
                        if(isset($data[$val[0]]))
                            if(false === $this->validationField($data, $val))
                                return false;
                }

            }
            // 批量验证的时候最后返回错误
            if(!empty($this->_error)) return false;
        }
        return true;
    }

    /**
     * 验证表单字段 支持批量验证
     * 如果批量验证返回错误的数组信息
     * @param  array   $data 创建数据
     * @param  array   $val  验证因子
     * @return boolean
     */
    private function validationField($data, $val) {
        if($this->_patchValidate && isset($this->_error[$val[0]]))
            return null; //当前字段已经有规则验证没有通过
        if(false === $this->validationFieldItem($data, $val)){
            if($this->_patchValidate) {
                $this->_error[$val[0]]   =   $val[2];
            }else{
                $this->_error            =   $val[2];
                return false;
            }
        }
        return null;
    }

    /**
     * 根据验证因子验证字段
     * @param  array $data 创建数据
     * @param  array $val  验证因子
     * @return boolean
     */
    private function validationFieldItem($data, $val) {
        switch (strtolower(trim($val[4]))) {
            case 'callback':// 调用方法进行验证
                $args = isset($val[6]) ? (array)$val[6] : array();
                if(is_string($val[0]) && strpos($val[0], ','))
                    $val[0] = explode(',', $val[0]);
                if(is_array($val[0])){
                    // 支持多个字段验证
                    foreach($val[0] as $field)
                        $_data[$field] = $data[$field];
                    array_unshift($args, $_data);
                }else{
                    array_unshift($args, $data[$val[0]]);
                }
                return call_user_func_array(array($val[1][0], $val[1][1]), $args);
            case 'confirm': // 验证两个字段是否相同
                return $data[$val[0]] == $data[$val[1]];
            default:  // 检查附加规则
                return $this->check($data[$val[0]], $val[1], $val[4]);
        }
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     * @param  string $value 验证数据
     * @param  mixed  $rule  验证表达式
     * @param  string $type  验证方式 默认为正则验证
     * @return boolean
     */
    public function check($value, $rule, $type = 'regex'){
        $type   =   strtolower(trim($type));
        switch($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range   = is_array($rule)? $rule : explode(',',$rule);
                return $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if (is_array($rule)){
                    $min    =    $rule[0];
                    $max    =    $rule[1];
                }else{
                    list($min, $max)   =  explode(',', $rule);
                }
                return $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                return $type == 'equal' ? $value == $rule : $value != $rule;
            case 'length': // 验证长度
                $length  =  mb_strlen($value,'utf-8'); // 当前数据长度
                if(strpos($rule, ',')) { // 长度区间
                    list($min, $max)   =  explode(',', $rule);
                    return $length >= $min && $length <= $max;
                }else{// 指定长度
                    return $length == $rule;
                }
            case 'expire':
                list($start,$end)   =  explode(',',$rule);
                if(!is_numeric($start)) $start   =  strtotime($start);
                if(!is_numeric($end)) $end   =  strtotime($end);
                return $_SERVER['REQUEST_TIME'] >= $start && $_SERVER['REQUEST_TIME'] <= $end;
            case 'ip_allow': // IP 操作许可验证
                return in_array($this->get_client_ip(),explode(',', $rule));
            case 'ip_deny':  // IP 操作禁止验证
                return !in_array($this->get_client_ip(),explode(',', $rule));
            case 'regex':
            default:    // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                return $this->regex($value,$rule);
        }
    }

    /**
     * 获取和设置语言定义(不区分大小写)
     * @param  string|array $name  语言变量
     * @param  string       $value 语言值
     * @return mixed
     */
    private function L($name=null, $value=null) {
        static $_lang = array();
        // 空参数返回所有定义
        if (empty($name))
            return $_lang;
        // 判断语言获取(或设置)
        // 若不存在,直接返回全大写$name
        if (is_string($name)) {
            $name = strtoupper($name);
            if (is_null($value))
                return isset($_lang[$name]) ? $_lang[$name] : $name;
            $_lang[$name] = $value; // 语言定义
            return null;
        }
        // 批量定义
        if (is_array($name))
            $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
        return null;
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    private function get_client_ip($type = 0,$adv=false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * 使用正则验证数据
     * @param  string $value 要验证的数据
     * @param  string $rule  验证规则
     * @return boolean
     */
    private function regex($value,$rule) {
        $validate = array(
            'require'   =>  '/\S+/',
            'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
            'currency'  =>  '/^\d+(\.\d+)?$/',
            'number'    =>  '/^\d+$/',
            'zip'       =>  '/^\d{6}$/',
            'integer'   =>  '/^[-\+]?\d+$/',
            'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
            'english'   =>  '/^[A-Za-z]+$/',
        );
        // 检查是否有内置的正则表达式
        if(isset($validate[strtolower($rule)]))
            $rule       =   $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
    }
}