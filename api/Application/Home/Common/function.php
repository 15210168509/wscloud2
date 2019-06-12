<?php
/**
 * Created by PhpStorm.
 * User: 02
 * Date: 2016/12/19
 * Time: 11:57
 */

/**
 * 将对象转换成数组
 * @param  object $obj
 * @return array
 */
function objectToArray($obj){
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr)){
        return array_map(__FUNCTION__, $arr);
    }else{
        return $arr;
    }
}

/**
 * 二维数组排序，类似MySQL的Order by排序，会根据第一个条件进行排序，当第一个排序结果相同时，按照第二个条件进行排序，以此类推
 * @param  array $rowset 二维数组
 * @param  array $args   排序规则数组，array('排序字段'=>'排序规则'[SORT_ASC升序|SORT_DESC降序])
 * @return array
 */
function sortByMultiCols($rowset, $args) {

    if (empty($args)) { return $rowset; }

    $sortArray = array();
    $sortRule = '';

    // 保存索引
    foreach ($rowset as $key=>&$val) {
        $val['temp_sort_key'] = strval($key);
    }
    // 排序
    foreach ($args as $sortField => $sortDir)
    {
        foreach ($rowset as $offset => $row)
        {
            $sortArray[$sortField][$offset] = $row[$sortField];
        }
        $sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
    }
    if (empty($sortArray) || empty($sortRule)) { return $rowset; }
    eval('array_multisort(' . $sortRule . '$rowset);');
    // 恢复索引
    $_rowset = array();
    foreach ($rowset as $key=>&$val) {
        $index = $val['temp_sort_key'];
        unset($val['temp_sort_key']);
        $_rowset[$index] = $val;
    }
    return $_rowset;
}

function num2str($num,$length){
    $num_str = (string)$num;
    $num_strlength = count($num_str);
    if ($length > $num_strlength) {
        $num_str=str_pad($num_str,$length,"0",STR_PAD_LEFT);
    }
    return $num_str;

}

/**
 * post请求
 * author 李文起
 * @param $url
 * @param $param
 * @return mixed
 */
function httpPostRequest($url,$param){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return_data = curl_exec($ch);
    curl_close($ch);

    return $return_data;
}

function getSettingName($type)
{
    $name = '';
    switch ($type){
        case '10':
            $name = '低速报警';
            break;
        case '20':
            $name = '警报声音';
            break;
        case '30':
            $name = '抽烟报警';
            break;
        case '40':
            $name = '打电话报警';
            break;
        case '50':
            $name = '左顾右盼报警';
            break;
        case '60':
            $name = '低头角度';
            break;
        case '70':
            $name = '左顾右盼延时';
            break;
        case '80':
            $name = '左顾角度';
            break;
        case '90':
            $name = '右盼角度';
            break;
        case '100':
            $name = '闭眼延时';
            break;
        case '110':
            $name = '低头报警间隔';
            break;
        case '120':
            $name = '闭眼报警间隔';
            break;
        case '130':
            $name = '打哈欠报警间隔';
            break;
        case '140':
            $name = '抽烟报警间隔';
            break;
        case '150':
            $name = '打电话报警间隔';
            break;
        case '160':
            $name = '左顾右盼报警间隔';
            break;
        case '170':
            $name = '抽烟延时';
            break;
        case '180':
            $name = '打电话延时';
            break;
        case '190':
            $name = '低头延时';
            break;
        case '200':
            $name = 'NV21图片';
            break;
        case 'app_versionName':
            $name = 'APP版本号';
            break;
        case 'runningTime':
            $name = '设备运行时长';
            break;
        case 'YawCalibrationOffset':
            $name = '标定左右角度偏移';
            break;
        case 'PitchCalibrationOffset':
            $name = '标定上下角度偏移';
            break;
        default:
            ;
    }
    return $name;
}

function getSettingValue($type,$value)
{
    $valueStr = $value;
    switch ($type){
        case '10':
            if ($value == 1) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;
        case '20':
            if ($value == 0) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;
        case '30':
            if ($value == 0) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;
        case '40':
            if ($value == 0) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;
        case '50':
            if ($value == 0) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;
        case '60':
            $valueStr = $value.'度';
            break;
        case '70':
            $valueStr = $value.'秒';
            break;
        case '80':
            $valueStr = $value.'度';
            break;
        case '90':
            $valueStr = $value.'度';
            break;
        case '100':
            $valueStr = $value.'秒';
            break;
        case '110':
            $valueStr = $value.'秒';
            break;
        case '120':
            $valueStr = $value.'秒';
            break;
        case '130':
            $valueStr = $value.'秒';
            break;
        case '140':
            $valueStr = $value.'秒';
            break;
        case '150':
            $valueStr = $value.'秒';
            break;
        case '160':
            $valueStr = $value.'秒';
            break;
        case '170':
            $valueStr = $value.'秒';
            break;
        case '180':
            $valueStr = $value.'秒';
            break;
        case '190':
            $valueStr = $value.'秒';
            break;
        case '200':
            if ($value == 0) {
                $valueStr = '关';
            } else {
                $valueStr = '开';
            }
            break;

        case 'runningTime':

            $valueStr = secToTime($value);
            break;
        case 'YawCalibrationOffset':
            $valueStr = $value.'度';
            break;
        case 'PitchCalibrationOffset':
            $valueStr = $value.'度';
            break;
        default:
            ;
    }
    return $valueStr;
}

/**
 *      把秒数转换为时分秒的格式
 *      @param Int $times 时间，单位 秒
 *      @return String
 */
function secToTime($times){
    $result = '00时00分00秒';
    if ($times>0) {
        $hour = floor($times/3600);
        $minute = floor(($times-3600 * $hour)/60);
        $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);
        $result = $hour.'时'.$minute.'分'.$second.'秒';
    }
    return $result;
}

/**
 * 阿里云文件路径转换
 */
function getOssFileUrl($path, $fileType=null) {
    switch ($fileType) {
        case 'img':
            $url = C('IMG_FILE_SERVER') . '/' . $path; break;
        case 'nv21':
            $url = C('NV21_FILE_SERVER') . '/' . $path; break;
        case 'video':
            $url = C('VIDEO_FILE_SERVER') . '/' . $path; break;
        default:
            $url = C('PRO_FILE_SERVER') . '/' . $path; break;
    }
    return $url;
}
