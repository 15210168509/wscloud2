<?php

namespace Home\Controller;

use Lib\Code;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Lib\Ws\WsClient;
use Think\Model;
use Lib\CommonConst;
use Lib\Tools;
use Lib\SmsService;
use Lib\GpsConvert;

/**
 * Class AdminController
 * @package Home\Controller
 */
class RoadLineController extends AdvancedRestController
{

    public function index()
    {
        die('接口，禁止直接访问');
    }

    /**
     * 添加路线
     */
    public function addRoadLine($adminId)
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

            $data['id'] = Tools::generateId();
            $data['name'] = $pare['name'];
            $data['point'] = $pare['point'];
            $data['company_id'] = $pare['company_id'];
            $data['create_user'] = $adminId;
            $data['update_user'] = $adminId;

            $model = D('RoadLine');
            if (!$model->create($data,Model::MODEL_INSERT)) {
                $this->setReturnVal(Code::ERROR,$model->getError(),StatusCode::DATA_ERROR);
            } else {
                $res = $model->add();
                if ($res) {
                    $this->setReturnVal(Code::OK,Msg::ADD_SUCCESS,StatusCode::ADD_SUCCESS);
                } else {
                    $this->setReturnVal(Code::ERROR,Msg::ADD_ERROR,StatusCode::ADD_ERROR);
                }
            }
        } else {
            $this->setReturnVal(Code::ERROR, Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 检索车辆
     * @param $keywords
     * @param $companyId
     */
    public function searchVehicle($keywords,$companyId)
    {
        $map['vehicle_no'] = array('like','%'.$keywords.'%');
        $map['del_flg'] = array('EQ',CommonConst::DEL_FLG_OK);
        $map['company_id'] = array('EQ',$companyId);
        $model = D('Vehicle');
        $res = $model->where($map)->select();
        if ($res) {
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 获取路线
     * @param $companyId
     */
    public function getRoad($companyId)
    {
        if (!empty($companyId)) {
            $model = D('RoadLine');
            $map['company_id'] = $companyId;
            $map['del_flg'] = CommonConst::DEL_FLG_OK;
            $res = $model->where($map)->select();
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 路线信息
     * @param $roadId
     */
    public function getRoadLineInfo($roadId)
    {
        if (!empty($roadId)) {
            $model = D('RoadLine');
            $map['id'] = $roadId;
            $res = $model->where($map)->find();
            if ($res) {
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 获取车辆坐标
     * @param $deviceNo
     */
    public function getVehicleLocation($deviceNo,$type = CommonConst::BD_MAP)
    {
        if (!empty($deviceNo)) {
            $redis = RedisLock::getInstance();
            $location = $redis->get($deviceNo);
            if ($type == CommonConst::BD_MAP) {
                $bd_location = GpsConvert::wgs84ToBd09($location['lng'],$location['lat']);
                $location['lng'] = $bd_location[0];
                $location['lat'] = $bd_location[1];
            }

            if ($location) {
                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$location);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    public function testGPS($lat,$lng){

        $gcjLocation = GpsConvert::wgs84ToBd09($lng,$lat);
        var_dump($gcjLocation);
    }
    /**
     * 车辆历史轨迹坐标
     * @param $startTime
     * @param $endTime
     * @param $deviceNo
     */
    public function getVehicleHistoryPoint($startTime,$endTime,$deviceNo,$type = CommonConst::BD_MAP)
    {
        if (!empty($deviceNo)) {

            $wsClient = WsClient::getInstance();
            $res = $wsClient->getTimeHorizonGps($startTime,$endTime,$deviceNo);
            if ($res['code'] == Code::OK) {
                //转换百度坐标
                if ($type == CommonConst::BD_MAP) {
                    foreach ($res['data'] as &$item) {
                        $bd_location  = GpsConvert::wgs84ToBd09($item['lng'],$item['lat']);
                        $item['org_lng'] = $item['lng'];
                        $item['org_lat'] = $item['lat'];
                        $item['lng'] = $bd_location[0];
                        $item['lat'] = $bd_location[1];
                    }
                }

                $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res['data']);
            } else {
                $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
            }
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

}