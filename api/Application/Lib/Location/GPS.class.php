<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/4
 * Time: 15:58
 */

namespace Lib\Location;


class GPS extends LocationInfo
{
    public function locationInfo() {

        $this->locationInfo['device_status'] = $this->deviceStatusByWeChatAppGPSHx($this->locationInfo);
        $this->locationInfo['heart_time']    = date('Y/m/d H:i:s',$this->locationInfo['gps_time']);
        $this->locationInfo['gps_time']       = date('Y/m/d H:i:s',$this->locationInfo['gps_time']);
        $this->locationInfo['direction']      = $this->diverDirection($this->locationInfo['course']);
        $this->locationInfo['device_info']   =  $this->deviceInfoByWeChatAppGPSHx($this->locationInfo);

        return $this->locationInfo;
    }
}