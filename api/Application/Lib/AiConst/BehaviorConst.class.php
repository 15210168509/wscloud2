<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/4/21
 * Time: 16:41
 */

namespace Lib\AiConst;


class BehaviorConst
{
    //行为类型
    const DISTRACTED           = 10;   //分心
    const TIRED                 = 20;   //疲劳
    const NORMAL                = 30;   //正常
    const ADAS                  = 40; //ADAS预警

    const ACTION_PHONE         = 10;    //打电话
    const ACTION_SMOKE         = 11;    //抽烟
    const ACTION_YAWN          = 12;    //打哈欠
    const ACTION_LOOK_AROUND  = 13;    //左顾右盼
    const ACTION_LOOK_DOWN     = 14;    //低头
    const ACTION_CLOSE_EYE     = 15;    //闭眼睛
    const ACTION_SHELTER_CAMERA = 16;   //遮挡相机
    const ACTION_NORMAL          = 17;   //正常
    const ACTION_NO_FACE          = 18;  //无人脸
    const ACTION_SUNGLASS         = 19;  //戴墨镜
    //============== ADAS 类型定义 ==============//
    const ADAS_LANE_DEPART  = 100; //车道偏离
    const ADAS_COLLISION    = 101;//前车碰撞预警
    const ADAS_DISTANCE     = 102;//保持车距
    const ADAS_START_UP     = 103;//前车启动


    //行为级别
    const LEVEL_SLIGHT          = 10;   //轻微
    const LEVEL_HEAVIER         = 20;   //较重
    const LEVEL_NORMAL          = 30;   //正常
    const LEVEL_HARD            = 40;   //严重
    const LEVEL_DANGEROUS       = 50;   //危险

    //行为文件类型
    const RECORD_TYPE_IMG      = 10;    //图片
    const RECORD_TYPE_VIDEO    = 20;    //视频

    //下载文件
    const SELECT_DATA_START     = 10;   //数据查询中
    const SELECT_DATA_ERROR     = 20;    //数据查询失败
    const SELECT_DATA_SUCCESS   = 30;    //数据查询成功
    const SELECT_BEHAVIOR_EMPTY = 40;   //行为列表为空
    const SELECT_DOWNLOAD_START = 50;   //开始压缩文件
    const SELECT_DOWNLOAD_SUCCESS = 60;   //压缩完成


    public static function getBehaviorType(){
        return array(
            '分心'=> array(
                self::ACTION_PHONE          => '打电话',
                self::ACTION_SMOKE          => '抽烟',
                self::ACTION_LOOK_AROUND    => '左右张望',
                self::ACTION_SHELTER_CAMERA => '遮挡相机',
            ),
            '疲劳'=>array(
                self::ACTION_YAWN           => '打哈欠',
                self::ACTION_LOOK_DOWN      => '低头',
                self::ACTION_CLOSE_EYE      => '闭眼睛',
            ),
            'ADAS'=>array(
                self::ADAS_COLLISION        => '前撞预警',
                self::ADAS_DISTANCE         => '车距太近',
                self::ADAS_LANE_DEPART      => '车道偏离',
                self::ADAS_START_UP         => '前车启动'
            )
        );
    }

    /**
     * 检测用户行为
     * author 李文起
     * @param $type
     * @return bool
     */
    public static function checkBehaviorType($type){
        return in_array($type,
            array(
                self::DISTRACTED,
                self::TIRED,
                self::ADAS
                )
        );
    }

    /**
     * 检测行为code
     * author 李文起
     * @param $code
     * @return bool
     */
    public static function checkBehaviorCode($code){
        return in_array($code,
            array(
                self::ACTION_PHONE,
                self::ACTION_SMOKE,
                self::ACTION_YAWN,
                self::ACTION_LOOK_AROUND,
                self::ACTION_LOOK_DOWN,
                self::ACTION_CLOSE_EYE,
                self::ACTION_SHELTER_CAMERA,
                self::ACTION_NORMAL,
                self::ACTION_NO_FACE,
                self::ACTION_SUNGLASS,
                self::ADAS_COLLISION,
                self::ADAS_DISTANCE,
                self::ADAS_LANE_DEPART,
                self::ADAS_START_UP
            )
        );
    }

    /**
     * 获取行为code
     * author 李文起
     * @return array
     */
    public static function getBehaviorCode(){
        return array(
            self::ACTION_PHONE,
            self::ACTION_SMOKE,
            self::ACTION_YAWN,
            self::ACTION_LOOK_AROUND,
            self::ACTION_LOOK_DOWN,
            self::ACTION_CLOSE_EYE,
            self::ACTION_SHELTER_CAMERA,
            self::ACTION_NORMAL,
            self::ACTION_NO_FACE,
            self::ACTION_SUNGLASS,
            self::ADAS_COLLISION,
            self::ADAS_DISTANCE,
            self::ADAS_LANE_DEPART,
            self::ADAS_START_UP
        );
    }

    /**
     * 检测行为级别
     * author 李文起
     * @param $level
     * @return bool
     */
    public static function checkBehaviorLevel($level){
        return in_array($level,
            array(
                self::LEVEL_SLIGHT,
                self::LEVEL_HEAVIER,
                self::LEVEL_NORMAL,
                self::LEVEL_HARD,
                self::LEVEL_DANGEROUS,
            )
        );
    }

    /**
     * 获取疲劳类型汉字
     * author 李文起
     * @param $type
     * @return string
     */
    public static function behaviorTypeStr($type){
        switch ($type) {
            case self::DISTRACTED  : return "分心";
            case self::TIRED       : return "疲劳";
            case self::NORMAL      : return "正常";
            case self::ADAS        : return "ADAS预警";
        }
    }

    /**
     * 行为编号转汉字
     * author 李文起
     * @param $code
     * @return string
     */
    public static function behaviorTypeCodeStr($code){
        switch ($code) {
            case self::ACTION_PHONE             : return "打电话";
            case self::ACTION_SMOKE             : return "抽烟";
            case self::ACTION_YAWN              : return "打哈欠";
            case self::ACTION_LOOK_AROUND       : return "分心驾驶";
            case self::ACTION_LOOK_DOWN         : return "分心驾驶";
            case self::ACTION_CLOSE_EYE         : return "闭眼睛";
            case self::ACTION_SHELTER_CAMERA    : return "遮挡相机";
            case self::ACTION_NORMAL             : return "正常";
            case self::ACTION_NO_FACE           : return "没有驾驶员";
            case self::ACTION_SUNGLASS          : return "墨镜遮挡";
            case self::ADAS_COLLISION           : return "前车碰撞预警";
            case self::ADAS_DISTANCE            : return "车距过近";
            case self::ADAS_LANE_DEPART         : return "车道偏离";
            case self::ADAS_START_UP            : return "前车启动";
            default: return '其他';
        }
    }

    /**
     * 行为编号转汉字
     * author 李文起
     * @param $code
     * @return string
     */
    public static function behaviorTypeCodeExportImage($code){
        switch ($code) {
            case self::ACTION_PHONE             : return "打电话";
            case self::ACTION_SMOKE             : return "抽烟";
            case self::ACTION_YAWN              : return "打哈欠";
            case self::ACTION_LOOK_AROUND       : return "左右张望";
            case self::ACTION_LOOK_DOWN         : return "低头";
            case self::ACTION_CLOSE_EYE         : return "闭眼睛";
            case self::ACTION_SHELTER_CAMERA    : return "遮挡相机";
            case self::ACTION_NORMAL            : return "正常";
            case self::ACTION_NO_FACE           : return "没有驾驶员";
            case self::ACTION_SUNGLASS          : return "墨镜遮挡";
            case self::ADAS_COLLISION           : return "前车碰撞预警";
            case self::ADAS_DISTANCE            : return "车距过近";
            case self::ADAS_LANE_DEPART         : return "车道偏离";
            case self::ADAS_START_UP            : return "前车启动";
            default: return '其他';
        }
    }

    /**
     * 用户行为级别汉字
     * author 李文起
     * @param $level
     * @return string
     */
    public static function behaviorLevelStr($level){
        switch ($level) {
            case self::LEVEL_SLIGHT     : return "轻微";
            case self::LEVEL_HEAVIER    : return "较重";
            case self::LEVEL_NORMAL     : return "正常";
            case self::LEVEL_HARD       : return "严重";
            case self::LEVEL_DANGEROUS  : return "危险";
        }
    }

    /**
     * 返回级别背景色
     * author 李文起
     * @param $level
     * @return string
     */
    public static function behaviorLevelBcColor($level){
        switch ($level) {
            case self::LEVEL_SLIGHT     : return "#FFA6FF";
            case self::LEVEL_HEAVIER    : return "#FF79BC";
            case self::LEVEL_NORMAL     : return "#96FED1";
            case self::LEVEL_HARD       : return "#FF7575";
            case self::LEVEL_DANGEROUS  : return "#EA0000";
        }
    }
}