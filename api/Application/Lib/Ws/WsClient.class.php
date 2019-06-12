<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/15
 * Time: 11:16
 */

namespace Lib\Ws;


use Lib\Logger\Logger;
use Lib\Ws\WsConnect\WsConnect;

class WsClient extends WsConnect
{
    const RestClient        = 'RestClient';
    const RestSyncClient    = 'RestSyncClient';
    private static $wsClient = null;

    public static function getInstance($restMethod = self::RestClient){
        if (self::$wsClient == null) {
            self::$wsClient = new WsClient($restMethod);
        }
        return self::$wsClient;
    }

    /**
     * 发送注册短信验证码
     * author 李文起
     * @param $phone
     * @return string
     */
    public function sendRegisterCode($phone){
        $res = $this->getResult('/OpenService/sendMobileVerificationCode/phone/'.$phone.'/label/1','get');
        return $res;
    }

    /**
     * 微视注册用户
     * author 李文起
     * @param $name
     * @param $phone
     * @param $account
     * @param $password
     * @param $code
     * @return string
     */
    public function userRegister($name,$phone,$account,$password,$code){
        $data['name']       = $name;
        $data['phone']      = $phone;
        $data['account']    = $account;
        $data['password']   = $password;
        $data['code']        = $code;

        $res = $this->getResult('/OpenService/userRegister','post',$data);
        return $res;
    }

    /**
     * 轮询
     * author 李文起
     * @return string
     */
    public function tiredValuePoll(){

        return $this->getResult('/Poll/tiredValuePoll','get');
    }

    /**
     * 获取GPS记录
     */
    public function getTimeHorizonGps($startTime, $endTime,$deviceNo)
    {
        return $this->getResult("/OpenService/getPositionBySafeDriver/startTime/$startTime/endTime/$endTime/deviceNo/$deviceNo", 'get');
    }

    /**
     * 获取疲劳值
     * author 李文起
     * @param $deviceNo
     * @param $startTime
     * @param $endTime
     * @param $limit
     * @return string
     */
    public function getTiredValueBySafePlatform($deviceNo,$startTime,$endTime,$limit=1){
        return $this->getResult('/OpenService/getTiredValueBySafePlatform/deviceNo/'.$deviceNo.'/startTime/'.$startTime.'/endTime/'.$endTime.'/limit/'.$limit,'get');
    }

    /**
     * 更新司机行为
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateBehavior($data){
        return $this->getResult('/UserBehaviorService/updateBehavior','post',$data);
    }

    /**
     * 获取司机行为列表
     * author 李文起
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @param string $startTime
     * @param string $endTime
     * @param string $name
     * @param string $phone
     * @param $code
     * @param $serialNo
     * @param $vehicleNo
     * @return string
     */
    public function getBehaviorList($companyId,$pageNo,$pageSize,$startTime,$endTime,$name,$phone,$code,$serialNo,$vehicleNo){
        return $this->getResult('/UserBehaviorService/getBehaviorList/safeCompanyId/'.$companyId.'/pageNo/'.$pageNo.'/pageSize/'.$pageSize.'/startTime/'.$startTime.'/endTime/'.$endTime.'/name/'.$name.'/phone/'.$phone.'/code/'.$code.'/serialNo/'.$serialNo.'/vehicleNo/'.$vehicleNo, 'get');
    }

    /**
     * 用户进行刷脸识别
     * author 李文起
     * @param $data
     * @return string
     */
    public function registerUserFace($data){
        return $this->getResult('/UserFace/registerUserFace','post',$data);
    }

    /**
     * 更新人脸照片
     * author 李文起
     * @param $data
     * @return string
     */
    public function updateUserFace($data){
        return $this->getResult('/UserFace/updateUserFace','post',$data);
    }

    /**
     * 更新用户帐号信息
     * author 李文起
     * @param $data
     * @return string
     */
    public function userUpdate($data){
        return $this->getResult('/OpenService/userUpdate','post',$data);
    }

    /**
     * 导出行为图片
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param $deviceNos
     * @param $code
     * @return string
     */
    public function exportBehaviorImages($companyId,$startTime,$endTime,$deviceNos,$code){
        return $this->getResult('/UserBehaviorService/exportBehaviorImages/safeCompanyId/'.$companyId.'/startTime/'.$startTime.'/endTime/'.$endTime.'/deviceNos/'.$deviceNos.'/code/'.$code);
    }
}