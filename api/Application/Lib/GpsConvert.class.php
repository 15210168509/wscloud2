<?php
namespace Lib;

class GpsConvert
{
    const x_PI  = 52.35987755982988;
    const PI  = 3.1415926535897932384626;
    const a = 6378245.0;
    const ee = 0.00669342162296594323;


    /**
     * 原始GPS84坐标专换为GCJ-02（火星，高德）坐标
     * @param $lng float 经度
     * @param $lat float 纬度
     * @return array(经度，维度)
     */
    static function wgs84ToGcj02($lng,$lat){
        $dlat = self::transformlat($lng - 105.0, $lat - 35.0);
        $dlng = self::transformlng($lng - 105.0, $lat - 35.0);
        $radlat = $lat / 180.0 * self::PI;
        $magic = sin($radlat);
        $magic = 1 - self::ee * $magic * $magic;
        $sqrtmagic = sqrt($magic);
        $dlat = ($dlat * 180.0) / ((self::a * (1 - self::ee)) / ($magic * $sqrtmagic) * self::PI);
        $dlng = ($dlng * 180.0) / (self::a / $sqrtmagic * cos($radlat) * self::PI);
        $mglat = $lat + $dlat;
        $mglng = $lng + $dlng;
        return array($mglng, $mglat);
    }
    /**
     * 原始GPS84坐标专换为baidu09 坐标
     * @param $lng float 经度
     * @param $lat float 纬度
     * @return array(经度，维度)
     */
    static function wgs84ToBd09($lng,$lat){
         $gcjLocation = self::wgs84ToGcj02($lng,$lat);
         return self::gcj02ToBd09($gcjLocation[0],$gcjLocation[1]);
    }
    /**
     * GCJ02坐标专换为baidu09 坐标
     * @param $lng float 经度
     * @param $lat float 纬度
     * @return array(经度，维度)
     */
    static function gcj02ToBd09($lng,$lat){
        $x = $lng; $y = $lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * self::x_PI);
        $theta = atan2($y, $x) + 0.000003 * cos($x * self::x_PI);
        $bdLon = $z * cos($theta) + 0.0065;
        $bdLat = $z * sin($theta) + 0.006;
        return array($bdLon,$bdLat);
    }


    private function transformlat($lng, $lat) {
        $ret = -100.0 + 2.0 * $lng + 3.0 * $lat + 0.2 * $lat * $lat + 0.1 * $lng * $lat + 0.2 * sqrt(abs($lng));

        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;

        $ret += (20.0 * sin($lat * self::PI) + 40.0 * sin($lat / 3.0 * self::PI)) * 2.0 / 3.0;

        $ret += (160.0 * sin($lat / 12.0 * self::PI) + 320 * sin($lat * self::PI / 30.0)) * 2.0 / 3.0;

        return $ret;
    }
    private function transformlng($lng, $lat) {
        $ret = 300.0 + $lng + 2.0 * $lat + 0.1 * $lng * $lng + 0.1 * $lng * $lat + 0.1 * sqrt(abs($lng));
        $ret += (20.0 * sin(6.0 * $lng * self::PI) + 20.0 * sin(2.0 * $lng * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($lng * self::PI) + 40.0 * sin($lng / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($lng / 12.0 * self::PI) + 300.0 * sin($lng / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }
}