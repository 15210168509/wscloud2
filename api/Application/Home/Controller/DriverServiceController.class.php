<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 16:15
 */

namespace Home\Controller;


use Lib\AiConst\BehaviorConst;
use Lib\Code;
use Lib\CommonConst;
use Lib\Logger\Logger;
use Lib\Mns\MnsTool;
use Lib\Mqtt\MsgPublish;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Lib\Tools;
use Lib\Ws\WsClient;
use Think\Model;

class DriverServiceController extends AdvancedRestController
{


    /**
     * 检测手机号是否存在
     * author 李文起
     * @param $phone
     * @param string $driverId            用户id存在则检查除id外手机号，不存在检查所有手机号
     * @return bool                     true 存在，false不存在
     */
    private function checkPhone($phone,$driverId = "null"){

        $model = D('Driver');

        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['phone'] = $phone;

        if ($driverId != "null"){
            $map['id'] = array('NEQ',$driverId) ;
        }
        $res = $model->where($map)->find();

        if (empty($res)){
            return false;
        }
        return true;
    }

    /**
     * 验证用户名是否存在
     * author 李文起
     * @param $account
     * @param $driverId
     * @return bool
     */
    private function checkAccount($account,$driverId="null"){
        $model = D('Driver');

        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['account'] = $account;

        if ($driverId != "null"){
            $map['id'] = array('NEQ',$driverId) ;
        }

        $res = $model->where($map)->find();

        if (empty($res)){
            return false;
        }
        return true;
    }

    /**
     * 微视注册
     * author 李文起
     * @param $phone
     */
    public function sendWsVerificationCode($phone){
        $wsClient = WsClient::getInstance();
        $res = $wsClient->sendRegisterCode($phone);
        $this->setReturnVal($res['code'],$res['msg'],$res['status_code']);
        $this->restReturn();
    }

    /**
     * 添加司机
     * author 李文起
     * @param $adminId
     */
    public function addDriver($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (isset($param['name']) && isset($param['phone']) && isset($param['account']) && isset($param['password']) && isset($param['certification_code'])
            && isset($param['certification_expire_time']) && isset($param['sex'])) {


            $wsClient = WsClient::getInstance();
            $res = $wsClient->userRegister($param['name'],$param['phone'],$param['account'],$param['password'],$param['code']);

            //如果AI平台绑定用户成功
            if ( $res['status_code'] == StatusCode::AI_USER_REGISTER_SUCCESS) {
                $this->addDriverInfo($adminId,$param,$res['data']['open_id']);

                //如果AI平台用户已存在
            } else if ($res['status_code'] == StatusCode::AI_USER_EXIST ) {

                //检测司机原在公司，如果存在并删除原在公司
                $checkRes = $this->checkDriverInfo($param,$res['data']['open_id']);

                if ($checkRes) {
                    //添加司机信息
                    $this->addDriverInfo($adminId,$param,$res['data']['open_id']);
                }

            } else {
                $this->setReturnVal($res['code'],$res['msg'],$res['status_code']);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 添加司机
     * author 李文起
     * @param $adminId
     * @param $param
     * @param $openId
     */
    private function addDriverInfo($adminId,$param,$openId){

        //添加本地数据
        $model = D('Driver');

        $param['id']             = Tools::generateId();
        $param['create_user']   = $adminId;
        $param['update_user']   = $adminId;
        $param['password']      = md5($param['password']);
        $param['ws_open_id']     = $openId;

        if (!$model->create($param,Model::MODEL_INSERT)) {
            $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
        } else {
            $res = $model->add();
            if ($res !== false) {
                //设置缓存
                $redis = RedisLock::getInstance();
                $arr['company_id'] = $param['company_id'];
                $arr['name'] = $param['name'];
                $arr['driver_id'] = $param['id'];
                $redis->set('safe_'.$param['ws_open_id'],json_encode($arr),0);

                $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
            }
        }
    }

    /**
     * 检测司机是否已在监控平台存在
     * author 李文起
     * @param $param
     * @param $openId
     * @return bool
     */
    public function checkDriverInfo($param,$openId){

        //添加本地数据
        $model = D('Driver');

        $map['ws_open_id']     = $openId;
        $map['del_flg']         = CommonConst::DEL_FLG_OK;

        $driverInfo = $model->where($map)->find();
        if (!empty($driverInfo)) {

            //判断是否为同一个公司
            if ( $driverInfo['company_id']  == $param['company_id']) {
                $this->setReturnVal(Code::ERROR,Msg::DRIVER_EXIST,StatusCode::DRIVER_EXIST);
                return false;

                //如果不是同一个公司
            } else {

                //删除司机原在公司
                $data['del_flg']       = CommonConst::DEL_FLG_DELETED;
                if (!$model->create($data,Model::MODEL_UPDATE)) {
                    $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                    return false;
                } else {
                    $res = $model->where($map)->save();
                    if ($res === false) {
                        $this->setReturnVal(Code::ERROR,Msg::DEL_NO,StatusCode::DEL_NO);
                        return false;
                    }
                }
            }
        }

        return true;

    }

    /**
     * author 李文起
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @param string $name
     * @param string $phone
     * @param string $status
     */
    public function driverLists($companyId,$pageNo, $pageSize, $name = 'null', $phone = 'null', $status = 'null')
    {
        $model = D('Driver');

        $map['del_flg']     = array('EQ', CommonConst::DEL_FLG_OK);
        $map['company_id']  = $companyId;

        if (isset($name) && $name != 'null') {
            $map['name'] = array('LIKE', '%'.addslashes($name).'%');
        }
        if (isset($phone) && $phone != 'null') {
            $map['phone'] = array('LIKE', '%'.addslashes($phone).'%');
        }
        if (isset($status) && $status != 'null') {
            $map['status'] = array('EQ', addslashes($status));
        }

        $totalRecord = $model
            ->where($map)
            ->count();

        $num = ceil($totalRecord/$pageSize);
        if ($pageNo > $num) {
            $pageNo = $num;
        }

        $result = $model
            ->field('*')
            ->where($map)
            ->page($pageNo, $pageSize)
            ->order('create_time DESC')
            ->select();

        if (count($result) > 0) {

            foreach ($result as &$value) {
                $value['certification_expire_time'] = $value['certification_expire_time'] ? date('Y-m-d', $value['certification_expire_time']) : '无';
                $value['create_time'] = $value['create_time'] ? date('Y-m-d H:i:s', $value['create_time']) : '无';
                $value['status_name'] = Status::AdminStatus2Str($value['status']);
                $value['face_path']   = $value['face_path'] ? C('OSS_DRIVER_FACE_IMG_PATH').$value['face_path']:'';
                $value['sex_name']    =  $value['sex'] == 1 ? '男':'女';
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 更新司机信息
     * author 李文起
     * @param $data
     * @param $id
     * @param $updateId
     */
    private function updateDriverInfo($data,$id,$updateId){
        $model = D('Driver');

        $map['id']              = $id;
        $map['del_flg']         = CommonConst::DEL_FLG_OK;
        $map['company_id']     = $data['company_id'];

        $data['update_user']   = $updateId;

        $driverRes = $model->field('ws_open_id')->where($map)->find();

        if (!$model->create($data,Model::MODEL_UPDATE)) {
            $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
        } else {
            $res = $model->where($map)->save();
            if ($res !== false) {


                $redis = RedisLock::getInstance();

                //删除reidis所存的司机信息
                if ($data['del_flg'] == CommonConst::DEL_FLG_DELETED) {
                    $redis->delete('safe_'.$driverRes['ws_open_id']);

                    //更新reidis所存的司机信息
                } else {
                    $driverInfo = $redis->get('safe_'.$driverRes['ws_open_id']);
                    $driverInfo['name'] = $data['name'];

                    $redis->set('safe_'.$driverRes['ws_open_id'],json_encode($driverInfo),0);
                }


                $this->setReturnVal(Code::OK,Msg::UPDATE_SUCCESS,StatusCode::UPDATE_SUCCESS);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
            }
        }
    }

    /**
     * 更新司机信息
     * author 李文起
     * @param $adminId
     */
    public function updateDriver($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (isset($param['name']) && isset($param['phone']) && isset($param['account'])) {

            //检测用户名
            $res = $this->checkAccount($param['account'],$param['id']);
            if (!$res) {

                //检测手机号
                $res = $this->checkPhone($param['phone'],$param['id']);
                if (!$res) {

                    //获取司机信息
                    $driverInfo = $this->getDriverInfo($param['id'],$param['company_id']);
                    //更新ai数据库中的数据
                    $data['account']    = $param['account'];
                    $data['phone']      = $param['phone'];
                    $data['name']       = $param['name'];
                    $data['openId']     = $driverInfo['ws_open_id'];

                    $wsClient = WsClient::getInstance();
                    $res = $wsClient->userUpdate($data);

                    if ($res['code'] == Code::OK){
                        //修改信息
                        $this->updateDriverInfo($param,$param['id'],$adminId);
                    } else {
                        $this->setReturnVal(Code::ERROR,$res['msg'],$res['status_code']);
                    }

                } else {
                    $this->setReturnVal(Code::ERROR,Msg::PHONE_EXIST,StatusCode::PHONE_EXIST);
                }

            } else {
                $this->setReturnVal(Code::ERROR,Msg::ACCOUNT_EXIST,StatusCode::ACCOUNT_EXIST);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 删除司机
     * author 李文起
     * @param $adminId
     */
    public function deleteDriver($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if (isset($param['id']) && isset($param['company_id'])) {
            //修改信息
            $this->updateDriverInfo($param,$param['id'],$adminId);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 查询司机信息
     * author 李文起
     * @param $id
     * @param $companyId
     * @return array
     */
    private function getDriverInfo($id,$companyId){

        $model = D('Driver');

        $map['id']           = $id;
        $map['del_flg']     = CommonConst::DEL_FLG_OK;
        $map['company_id']  = $companyId;

        $res = $model->field('id,name,sex,phone,account,ws_open_id,certification_code,certification_expire_time,driving_age,status')->where($map)->find();
        return $res;
    }

    /**
     * 通过OpenId获取司机信息
     * author 李文起
     * @param $openId
     * @return mixed
     */
    private function getDriverInfoByOpenId($openId){
        $model = D('Driver');

        $map['ws_open_id']  = $openId;
        $map['del_flg']     = CommonConst::DEL_FLG_OK;

        $res = $model->field('id,company_id,name,sex,phone,account,ws_open_id,certification_code,certification_expire_time,driving_age,status')->where($map)->find();
        return $res;
    }

    /**
     * 获取司机信息
     * author 李文起
     * @param $id
     * @param $companyId
     */
    public function driverInfo($id,$companyId){

        if (is_numeric($id) && is_numeric($companyId)) {

            $res = $this->getDriverInfo($id,$companyId);
            if (!empty($res)) {

                $res['certification_expire_time'] = $res['certification_expire_time'] ? date('Y-m-d', $res['certification_expire_time']) : '无';
                $res['create_time'] = $res['create_time'] ? date('Y-m-d H:i:s', $res['create_time']) : '无';
                $res['status_name'] = Status::AdminStatus2Str($res['status']);
                $res['sex_name']    =  $res['sex'] == 1 ? '男':'女';

                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }


    /**
     * 获取设置项值
     * author 李文起
     * @param $companyId
     * @param $type
     * @return mixed
     */
    private function getSystemSetting($companyId,$type) {
        $model = D('SystemSetting');

        $map['del_flg']     = CommonConst::DEL_FLG_OK;
        $map['company_id']  = $companyId;
        $map['type']        = $type;

        $res = $model->where($map)->find();
        return $res;
    }

    /**
     * 司机行为监控
     * author 李文起
     */
    public function driverBehaviorMonitor(){

        if (C('ENVIRONMENT') == 'release') {
            $msg = json_decode(file_get_contents("php://input"),true);
            $param = json_decode($msg['Message'],true);
        } else{
            $param = $_POST;
        }

        if (isset($param) && !empty($param)) {

            $redis = RedisLock::getInstance();

            //获取设备绑定信息
            $deviceInfo = $redis->get('safe_device_'.$param['device_serial_no']);

            //发送mqtt消息
            $warningSetting = $redis->get('safe_monitor_type_'.$deviceInfo['company_id']);
            if (!empty($warningSetting)) {
                if (in_array($param['code'], explode(',', $warningSetting))) {


                    $param['driver_name'] = '未知司机';
                    //如果有司机open_id
                    if ($param['open_id']) {
                        $driverInfo = $redis->get('safe_' . $param['open_id']);

                        //设备和司机同属一个公司
                        if ($driverInfo['company_id'] == $deviceInfo['company_id']) {
                            $param['driver_name'] = $driverInfo['name'];
                        }
                    }


                    //处理预警数据
                    $param['vehicle_no']    = !empty($deviceInfo['vehicle_no']) ? $deviceInfo['vehicle_no'] : '未知车辆' ;
                    $param['type_text']     = BehaviorConst::behaviorTypeStr($param['type']);
                    $param['level_text']    = BehaviorConst::behaviorLevelStr($param['level']);
                    $param['level_color']    = BehaviorConst::behaviorLevelBcColor($param['level']);
                    $param['code_text']     = BehaviorConst::behaviorTypeCodeStr($param['code']);
                    $param['kmh']            = $param['speed'] ? $param['speed'] : 0;
                    $param['time']           = date('Y-m-d H:i:s', $param['location_time']);
                    $param['time_text']      = date('H:i:s', $param['location_time']);
                    $param['location']       = !empty($param['location_lng']) && !empty($param['location_lat']) ? $this->getMapLocation($param['location_lng'], $param['location_lat']) : '未知';

                    //获取管理员列表
                    $admins = $redis->get('safe_admin_'.$deviceInfo['company_id']);
                    foreach ($admins as $key => $value) {
                        MsgPublish::getInstance()->sendMsg(CommonConst::TOPIC_ADMIN . '/' . $value['phone'], json_encode(array('msgType' => CommonConst::MSG_TYPE_TIRED, 'data' => $param)));
                    }


                    $this->setReturnVal(Code::OK, Msg::ADD_SUCCESS, StatusCode::ADD_SUCCESS);

                }
            } else {
                $this->setReturnVal(Code::OK, Msg::ADD_SUCCESS, StatusCode::ADD_SUCCESS);
            }

        }else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING, StatusCode::PARA_MISSING);
        }
        $this->restReturn();

    }

    /**
     * 获取司机信息
     * author 李文起
     */
    public function getDriverCompanyInfo() {
        $param = $_POST;
        if (isset($param)) {
            //获取司机信息
            $redis = RedisLock::getInstance();

            //获取设备信息
            $deviceInfo = $redis->get('safe_device_'.$param['device_serial_no']);

            $data['company_id']     = $deviceInfo['company_id'];
            $data['vehicle_no']     = $deviceInfo['vehicle_no'];

            //如果有司机open_id
            if ($param['open_id']){
                $driverInfo = $redis->get('safe_'.$param['open_id']);

                //设备和司机同属一个公司
                if ($driverInfo['company_id'] == $deviceInfo['company_id']) {
                    $data['driver_id']      = $driverInfo['driver_id'];
                    $data['driver_name']   = $driverInfo['name'];
                }
            }

            $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$data);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();


    }

    /**
     * 百度地图坐标反查
     */
    private function getMapLocation($lng, $lat,$type='gaode')
    {

        if (!empty($lat) && !empty($lng)) {

            switch ($type) {
                case 'baidu': // 百度坐标系
                    $url = 'http://api.map.baidu.com/geocoder/v2/?ak=mbxCCTHApgXL9heLp0RMxOoY&location='.$lat.','.$lng.'&output=json&pois=1';
                    break;
                case 'gaode': // 高德坐标系
                    // 经纬度处理
                    $lng = explode('.', $lng)[0].'.'.mb_substr(explode('.', $lng)[1], 0, 6);
                    $lat = explode('.', $lat)[0].'.'.mb_substr(explode('.', $lat)[1], 0, 6);
                    $url = 'http://restapi.amap.com/v3/geocode/regeo?key=70f617e1b16c11e6e845ffe656d65d0f&location='.$lng.','.$lat.'&radius=1000&extensions=all&batch=false&roadlevel=0';
                    break;
                default:
                    return '未知';
            }

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);

            if ($result) {
                $result = json_decode($result, true);
                switch ($type) {
                    case 'baidu': // 百度坐标系
                        return $result['result']['formatted_address'];
                    case 'gaode': // 高德坐标系
                        return $result['regeocode']['roads'][0]['name'] . '/' . $result['regeocode']['roads'][0]['direction'] . '/' . $result['regeocode']['roads'][0]['distance'] . '米';
                    default:
                        return '未知';
                }
            }
        }
        return '未知';
    }

    /**
     * 上传司机人脸照片
     * author 李文起
     * @param $adminId
     */
    public function uploadFace($adminId){
        $param = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if (isset($param['img']) && isset($param['driverId']) && isset($param['fileType'])) {

            $model = D('Driver');

            $map['id']          = $param['driverId'];
            $map['del_flg']    = CommonConst::DEL_FLG_OK;

            $res = $model->where($map)->find();
            if (isset($res['ws_open_id'])) {

                $data['open_id']      = $res['ws_open_id'];
                $data['image']        = $param['img'];
                $data['file_type']    = $param['fileType'];

                //判断是否上传过人脸图片
                $wsClient = WsClient::getInstance();
                if (empty($res['face_path'])) {
                    $res = $wsClient->registerUserFace($data);
                } else {
                    $res = $wsClient->updateUserFace($data);
                }


                if ($res['code'] == Code::OK){

                    //修改人脸已经上传
                    $driverData['update_user']    = $adminId;
                    $driverData['face_path']      = $res['data']['path'];

                    if (!$model->create($driverData,Model::MODEL_UPDATE)){
                        $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
                    } else {
                        $result = $model->where($map)->save();
                        if ($result !== false) {
                            $this->setReturnVal($res['code'],$res['msg'],$res['status_code'],$res['data']);
                        } else {
                            $this->setReturnVal(Code::ERROR,Msg::UPDATE_ERROR,StatusCode::UPDATE_ERROR);
                        }
                    }
                } else {
                    $this->setReturnVal($res['code'],$res['msg'],$res['status_code']);
                }

            } else {
                $this->setReturnVal(Code::ERROR,Msg::GET_DATA_ERROR,StatusCode::GET_DATA_ERROR);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }
}