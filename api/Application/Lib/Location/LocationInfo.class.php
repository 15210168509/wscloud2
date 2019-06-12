<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/4
 * Time: 15:59
 */

namespace Lib\Location;


class LocationInfo
{
    protected $locationInfo;

    public function __construct($locationInfo)
    {
        $this->locationInfo = $locationInfo;
    }


    /**
     * 获取device_info
     * author 李文起
     * @param $data
     * @return string
     */
    protected function deviceInfoByWeChatAppGPSHx($data){
        $deviceInfo = 0;
        if (time() - $data['sys_time'] >  1000 * 600 && $data['speed'] == 0) {
            $deviceInfo = 3;
        }
        return $deviceInfo;
    }

    /**
     * author 李文起
     * @param $data
     * @return string
     */
    protected function deviceStatusByWeChatAppGPSHx($data){
        if (time() - $data['sys_time'] >  1000 * 600  && $data['speed'] == 0) {
            $deviceStatus = '离线';
        } else if ($data['speed'] == 0) {
            $deviceStatus = '静止';
        } else {
            $deviceStatus = '运动';
        }
        return $deviceStatus;
    }

    /**
     * author 李文起
     * 判断车辆行驶的方向
     * @param $course   float   角度
     * @return string           中文方向
     */
    protected function diverDirection($course){
        $direction = '';
        if (0 == $course){
            $direction = '正北向';
        } else if( 0 <$course && $course<45) {
            $direction = '东北向偏北';
        } else if (45==$course) {
            $direction = '东北向';
        }else if (45<$course && $course< 90) {
            $direction = '东北向偏东';
        }else if ($course==90) {
            $direction = '正东向';
        } else if (90<$course && $course<135) {
            $direction = '东南向偏东';
        } else if (135 == $course) {
            $direction = '东南向';
        } else if (135<$course && $course<180){
            $direction = '东南向偏南';
        } else if ($course == 180) {
            $direction = '正南向';
        } else if (180<$course && $course<225){
            $direction = '西南向偏南';
        }else if (225 == $course) {
            $direction = '西南向';
        } else if (225<$course && $course<270){
            $direction = '西南向偏西';
        } else if (270 == $course) {
            $direction = '正西向';
        } else if (270<$course && $course<315){
            $direction = '西北向偏西';
        } else if (315 == $course) {
            $direction = '西北向';
        } else if (315<$course && $course<360){
            $direction = '西北向偏北';
        }
        return $direction;
    }
}