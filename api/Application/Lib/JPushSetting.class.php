<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/8/7
 * Time: 16:03
 */

namespace Lib;


use Lib\JPush\Client;
use Lib\JPush\Exceptions\APIConnectionException;
use Lib\JPush\Exceptions\APIRequestException;

class JPushSetting
{


    public static function getSettings($deviceType){
        if($deviceType != '') {
            if($deviceType == CommonConst::DEVICE_RUICHENG) {
                //锐承设备
                return array(C('JPUSH_APP_KEY'), C('JPUSH_MASTER_SECRET'));
            } elseif ($deviceType == CommonConst::DEVICE_DIPINGXIAN) {
                //地平线设备
                return array(C('JPUSH_APP_KEY2'), C('JPUSH_MASTER_SECRET2'));
            }
            return array();
        }
        return false;

    }

}