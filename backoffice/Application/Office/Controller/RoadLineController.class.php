<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Office\Controller;

use Lib\BaseManagementController;
use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;
use Lib\Status;
use Lib\StatusCode;
use Lib\Tools;

class RoadLineController extends ListManagementController
{

    public $authentication = true;

    /**
     * 添加路线
     */
    public function add()
    {
        $this->breadcrumb = array("规划路线"=>'#','添加路线'=>'add');
        $this->addJS(array('office/roadLine/add.js'));
        $this->addAllJS(array('http://api.map.baidu.com/api?v=2.0&ak='.C('BAIDU_API_KEY')));
        $this->display();
    }

    /**
     * 添加路线
     */
    public function createRoadLine()
    {
        $point = $_POST['point'];
        $data['name'] = I('post.name');
        if (!empty($data['name'] && !empty($point))) {
            $data['point'] = rtrim(str_replace('"','',$point),';');
            $data['company_id'] = $this->context->loginuser->company_id;
            $model = D('RoadLine');
            $res = $model->addRoadLine($data);
            $result = array('code'=>$res['code'],'msg'=>$res['msg']);
        } else {
            $result = array('code'=>CommonConst::CODE_ERROR,'msg'=>Msg::PARA_MISSING);
        }
        $this->ajaxReturn($result);
    }

    /**
     * 路线监控
     */
    public function roadLineMonitor()
    {
        $this->breadcrumb = array("规划路线"=>'#','路线监控'=>'roadLineMonitor');
        $this->addAllJS(array('http://api.map.baidu.com/api?v=2.0&ak='.C('BAIDU_API_KEY')));
        $this->addJS(array('office/roadLine/roadLineMonitor.js','office/roadLine/GeoUtils.js'));
        $model = D('RoadLine');
        $res = $model->getRoad($this->context->loginuser->company_id);
        $this->assign('road',$res['data']);
        $this->display();
    }

    /**
     * 实时位置追踪
     */
    public function realPositionMonitor()
    {
        $this->breadcrumb = array("规划路线"=>'#','实时位置'=>'realPositionMonitor');
        $this->addAllJS(array('http://api.map.baidu.com/api?v=2.0&ak='.C('BAIDU_API_KEY')));
        $this->addJS(array('office/roadLine/realPositionMonitor.js','office/roadLine/GeoUtils.js'));
        $this->display();
    }

    /**
     * 车辆检索
     * @param $keywords
     */
    public function searchVehicle($keywords)
    {
        $model = D('RoadLine');
        $res = $model->searchVehicle($keywords,$this->context->loginuser->company_id);
        $this->ajaxReturn(array('code'=>$res['code'],'content'=>$res['data']));
    }

    /**
     * 路线信息
     * @param $roadId
     */
    public function getRoadLineInfo($roadId)
    {
        $model = D('RoadLine');
        $res = $model->getRoadLineInfo($roadId);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']['point']));
    }

    /**
     * 获取车辆坐标信息
     * @param $deviceNo
     */
    public function getVehicleLocation($deviceNo)
    {
        $model = D('RoadLine');
        $res = $model->getVehicleLocation($deviceNo);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 历史轨迹
     */
    public function historyLine()
    {
        $this->addAllJS(array('http://api.map.baidu.com/api?v=2.0&ak='.C('BAIDU_API_KEY')));
        $this->addAllJS('http://api.map.baidu.com/library/CurveLine/1.5/src/CurveLine.min.js');
        $this->addJS(array('office/roadLine/historyLine.js','office/gps/gcoord.js','office/gps/LuShu.js','office/gps/bar.js','office/gps/car.js', 'office/gps/map.js',));
        $this->assign('initMinTime', date('Y-m-d H:i:s', strtotime(date('Y-m-d') . '-1 day')));
        $this->assign('initMaxTime', date('Y-m-d H:i:s'));
        $this->assign('minTime', date('Y-m-d', strtotime(date('Y-m-d') . '-30 day')));
        $this->assign('maxTime', date('Y-m-d'));
        $this->display();
    }

    /**
     * 获取车辆轨迹
     * @param $startTimeStr
     * @param $endTimeStr
     * @param $initFlg
     */
    public function getVehicleHistoryPoint()
    {
        $startTimeStr=I('post.startTimeStr');
        $endTimeStr = I('post.endTimeStr');
        $initFlg     = I('post.initFlg');
        $deviceNo = I('post.deviceNo');
        $model = D('RoadLine');
        $startTime = $startTimeStr != 'null' ? (strtotime($startTimeStr) ?  strtotime($startTimeStr) : 'null') : 'null';
        $endTime   = $endTimeStr != 'null' ? (strtotime($endTimeStr) ?  strtotime($endTimeStr) : 'null') : 'null';

        // 判断是否是初始调用
        if (is_numeric($startTime) && $initFlg != 'true') $startTime += 10;
        $res = $model->getVehicleHistoryPoint($startTime,$endTime,$deviceNo);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }




}