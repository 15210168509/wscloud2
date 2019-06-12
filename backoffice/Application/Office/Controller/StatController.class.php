<?php
namespace Office\Controller;
use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;
use Lib\StatusCode;
use Lib\Ws\WsClient;

/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/21
 * Time: 10:52
 */
class StatController extends ListManagementController
{
    public function statBehavior($startTime, $endTime)
    {
        if (isset($startTime) && isset($endTime)) {

            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);

            $model = D('DrivingMonitor');
            $res = $model->statByDayGroup($this->context->loginuser->company_id, $startTime, $endTime);

            $this->ajaxReturn(array('code' => Code::OK, 'msg' => $res['msg'], 'status_code' => $res['status_code'], 'data' => $res['data']));
        } else {
            $this->ajaxReturn(array('code' => Code::ERROR, Msg::PARA_MISSING, StatusCode::PARA_MISSING));
        }
    }

    /**
     *
     * 疲劳次数
     */
    public function showTiredNumber()
    {

        $endTime = time();
        if (I('post.timeType') == 30) {
            $startTime = time() - 3600 * 30 * 24;
        } else if (I('post.timeType') == 7) {
            $startTime = time() - 3600 * 30 * 7;
        } else {
            $startTime = time() - 3600 * 30 * 3;
        }
        $model = D('DrivingMonitor');
        $res = $model->statByDayGroup($this->context->loginuser->company_id, $startTime, $endTime);
        if ($res['code'] == Code::OK) {
            $data = [];
            foreach ($res['data'] as $k => $v) {
                $data['xAxis'][] = $k;
                $data['yAxis'][] = $v;
            }
            $this->ajaxReturn(array('code' => Code::OK, 'msg' => $res['msg'], 'data' => $data));
        } else {
            $this->ajaxReturn(array('code' => Code::ERROR, 'msg' => $res['msg']));
        }

    }

    /**
     * 行为类型统计
     */
    public function showTiredType()
    {
        $model = D('DrivingMonitor');
        if (I('post.timeType') == 30) {
            $startTime = time() - 3600 * 30 * 24;
        } else if (I('post.timeType') == 7) {
            $startTime = time() - 3600 * 30 * 7;
        } else if (I('post.timeType') == 3) {
            $startTime = time() - 3600 * 30 * 3;
        } else {
            $startTime = strtotime(date("Y-m-d", time()));
        }
        $endTime = time();
        $res = $model->showTiredType($startTime, $endTime, $this->context->loginuser->company_id);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']));
    }

    /**
     * 报警集中时间段
     */
    public function statByTimeGroup()
    {

        $endTime = strtotime(date("Y-m-d", time())) + 3600 * 24;
        if (I('post.timeType') == 30) {
            $startTime = strtotime(date("Y-m-d", (time() - 3600 * 24 * 30)));
        } else if (I('post.timeType') == 7) {
            $startTime = strtotime(date("Y-m-d", (time() - 3600 * 24 * 7)));
        } else {
            $startTime = strtotime(date("Y-m-d", (time() - 3600 * 24 * 3)));
        }
        $model = D('DrivingMonitor');
        $res = $model->statByTimeGroup($this->context->loginuser->company_id, $startTime, $endTime);

        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code' => Code::OK, 'msg' => $res['msg'], 'data' => $res['data']));
        } else {
            $this->ajaxReturn(array('code' => Code::ERROR, 'msg' => $res['msg']));
        }
    }

    /**
     * 疲劳值
     */
    public function showTiredValue()
    {
        $arr['device_no'] = array_unique(I('post.device'));
        $model = D('DrivingMonitor');
        $res = $model->showTiredValue($arr);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']));
    }

    /**
     * 车辆列表
     * author 李文起
     * @param string $groupId
     */
    public function getVehicleListByGroups($groupId = 'null')
    {
        $model = D('Vehicle');
        $res = $model->getVehicleListByGroups($this->context->loginuser->company_id, $groupId);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'status_code' => $res['status_code'], 'data' => $res['data']));
    }

    /**
     * 根据类型获取所有分组
     * author 李文起
     * @param string $type
     */

    /**
     * 实时报警次数（当天零点起）
     */
    public function statTiredNo()
    {
        $model = D('DrivingMonitor');
        $time = strtotime(date('Y-m-d',time()));
        $res = $model->statTiredNo($this->context->loginuser->company_id, $time);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']));
    }

    public function showVehicle()
    {
        $arr = I('post.device');
        $model = D('DrivingMonitor');
        $res = $model->showVehicle($arr);
        $time = time();
        $data = [];
        $data['driving']['north'] = [];
        $data['driving']['south'] = [];
        $data['driving']['west']  = [];
        $data['driving']['east']  = [];

        $data['stop']['north'] = [];
        $data['stop']['west']  = [];
        $data['stop']['south'] = [];
        $data['stop']['east']  = [];

        $data['offLine']['north'] = [];
        $data['offLine']['west'] = [];
        $data['offLine']['south'] = [];
        $data['offLine']['east'] = [];
        if ($res['code'] == Code::OK) {

            foreach ($res['data'] as $val) {

                $vehicle = [];
                if ($val['position_info']) {

                    $vehicleInfo = [];
                    $vehicleInfo['speed'] = $val['position_info']['speed'] ? $val['position_info']['speed'] : 0;
                    $vehicleInfo['gps_time'] = date('Y-m-d H:i:s', $val['position_info']['gps_time']);
                    $vehicleInfo['position'] = !empty($val['position_info']) ? $this->getMapLocation($val['position_info'][0], $val['position_info'][1]) : '未知';

                    $vehicle[] = $val['position_info'][0];
                    $vehicle[] = $val['position_info'][1];
                    $vehicle[] = $val['vehicle_no'];
                    $vehicle[] = $vehicleInfo;

                    if (($time - $val['position_info']['gps_time']) >= 1800) {
                        //离线
                        if ((275<=$val['position_info']['course']) || ($val['position_info']['course'] < 45)) {
                            $data['offLine']['north'][] = $vehicle;
                        }
                        if ((45<=$val['position_info']['course']) && ($val['position_info']['course'] < 135)) {
                            $data['offLine']['east'][] = $vehicle;
                        }
                        if ((135<=$val['position_info']['course']) && ($val['position_info']['course'] < 225)) {
                            $data['offLine']['south'][] = $vehicle;
                        }
                        if ((225<=$val['position_info']['course']) && ($val['position_info']['course'] < 275)) {
                            $data['offLine']['west'][] = $vehicle;
                        }

                    } else {
                        if ($vehicleInfo['speed'] > 0) {
                            //运动
                            if ((275<=$val['position_info']['course']) || ($val['position_info']['course'] < 45)) {
                                $data['driving']['north'][] = $vehicle;
                            }
                            if ((45<=$val['position_info']['course']) && ($val['position_info']['course'] < 135)) {
                                $data['driving']['east'][] = $vehicle;
                            }
                            if ((135<=$val['position_info']['course']) && ($val['position_info']['course'] < 225)) {
                                $data['driving']['south'][] = $vehicle;
                            }
                            if ((225<=$val['position_info']['course']) && ($val['position_info']['course'] < 275)) {
                                $data['driving']['west'][] = $vehicle;
                            }
                        } else {
                            //静止
                            if ((275<=$val['position_info']['course']) || ($val['position_info']['course'] < 45)) {
                                $data['stop']['north'][] = $vehicle;
                            }
                            if ((45<=$val['position_info']['course']) && ($val['position_info']['course'] < 135)) {
                                $data['stop']['east'][] = $vehicle;
                            }
                            if ((135<=$val['position_info']['course']) && ($val['position_info']['course'] < 225)) {
                                $data['stop']['south'][] = $vehicle;
                            }
                            if ((225<=$val['position_info']['course']) && ($val['position_info']['course'] < 275)) {
                                $data['stop']['west'][] = $vehicle;
                            }
                        }

                    }

                }

            }
        }

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$data));
    }

    public function typeGroupsLists($type = 'null')
    {
        $model = D('Groups');
        $res = $model->typeGroupsLists($this->context->loginuser->company_id, $type);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'status_code' => $res['status_code'], 'data' => $res['data']));
    }

    /**
     * 百度地图坐标反查
     */
    private function getMapLocation($lng, $lat,$type = 'gaode')
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
     * 统计报表，预警统计表
     *
    */
    public function warnStat(){
        $this->breadcrumb = array("统计报表"=>'/', "预警统计"=>"#");
        $this->addCSS(array('office/stat/userBehavior.css'=>'all'));
        $this->addJS(array('office/stat/warnexport.js'));
        $this->addAllJS('http://api.map.baidu.com/api?v=2.0&ak=mbxCCTHApgXL9heLp0RMxOoY');
        $this->addAllJS('https://g.alicdn.com/de/prismplayer/2.8.0/aliplayer-min.js');

        $this->assign('where_deviceNo',I('get.deviceNo'));
        $this->assign('where_vehicleNo',I('get.vehicleNo'));

        $this->display();
    }

    /**
     * 统计报表，上下线统计表
     *
     */
    public function onlineStat(){
        $this->breadcrumb = array("统计报表"=>'/', "预警统计"=>"#");
        $this->addCSS(array('office/stat/userBehavior.css'=>'all'));
        $this->addJS(array('office/stat/onlineexport.js'));
        $this->addAllJS('http://api.map.baidu.com/api?v=2.0&ak=mbxCCTHApgXL9heLp0RMxOoY');
        $this->addAllJS('https://g.alicdn.com/de/prismplayer/2.8.0/aliplayer-min.js');

        $this->assign('where_deviceNo',I('get.deviceNo'));
        $this->assign('where_vehicleNo',I('get.vehicleNo'));

        $this->display();
    }

    public function exportWarn($companyId = 'null',$companyName= 'null',$code,$vehicleNo,$deviceNo,$startTime,$endTime){

        $companyName            = $companyName!='null'?$companyName:$this->context->loginuser->company_name;
        $param['startTime']     = $startTime == 'null' ? 'null' : strtotime($startTime);
        $param['endTime']       = $endTime == 'null' ? 'null' : strtotime($endTime);
        $param['pageNo']        = 1;
        $param['pageSize']      = 100;
        $param['name']          = 'null';
        $param['phone']         = 'null';
        $param['code']          = $code;
        $param['vehicleNo']     = $vehicleNo == "null" ? '':$vehicleNo;
        $param['deviceNo']      = $deviceNo  == "null" ? '':$deviceNo;

        $this->setState('behavior_list_where',$param);

        $companyId = $companyId !='null'?$companyId:$this->context->loginuser->company_id;
        $model = D('DrivingMonitor');
        $res = $model->behaviorLists($companyId,$param['pageNo'],$param['pageSize'], $param['startTime'],$param['endTime'],$param['name'],$param['phone'],$param['code'],$deviceNo,$vehicleNo);

        if ($res['code'] == Code::OK && $res['data']['totalRecord'] > 0) {
            $exportData = array();
            foreach ($res['data']['dataList'] as $key=>&$value) {

                $tmpData['time']        = date('Y-m-d H:i:s', $value['location_time']);
                $tmpData['companyName'] = $companyName;
                $tmpData['carNo']       = $value['vehicle_no'];
                $tmpData['warnType']    = $value['type_text'];
                //坐标转换
                $point = $this->bd_encrypt($value['location_lng'],$value['location_lat']);
                $tmpData['speed']        = $value['speed'] ? $value['speed']*3.6 : 0;
                $lng                     = $value['location_lng'];
                $lat                     = $value['location_lat'];
                $value['location'] = !empty($lng) && !empty($lat) ? $this->getMapLocation($lng, $lat) : '未知';
                $tmpData['location']     = !empty($lng) && !empty($lat) ? $this->getMapLocation($lng, $lat) : '未知';
                $tmpData['lng']          = $lng;
                $tmpData['lat']          = $lat;
                $tmpData['handleMethod'] = '';
                $tmpData['handleResult'] = '';
                $tmpData['handleUser']   = '';
                $exportData[]            = $tmpData;
            }
            $filename = '预警台账'.date('Y-m-d H/i/s');
            $title    = '预警台账';
            $columns  = array('报警时间','车辆单位','车牌号','报警类型','车速(Km/h)','报警位置','经度','纬度','处置措施','处理结果','操作员');

            $this->exportExcel($filename,$title,$columns,$exportData);

        } else {

        }
    }
    //GCJ-02(火星，高德)坐标转换成BD-09(百度)坐标
    //@param bd_lon 百度经度
    //@param bd_lat 百度纬度
    private function bd_encrypt($gg_lon,$gg_lat){
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $gg_lon;
        $y = $gg_lat;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        // 保留小数点后六位
        $data['bd_lon'] = round($bd_lon, 8);
        $data['bd_lat'] = round($bd_lat, 8);
        return $data;
    }

    /**
     * 导出数据
     * author 李文起
     * @param $param
     */
    private function exportExcel($filename,$title,$columns,$param){

        //$fileName = '预警台账'.date('Y-m-d H/i/s');//or $xlsTitle 文件名称可根据自己情况设定
        $fileName = $filename;

        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new \PHPExcel();

        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $titleBig  = $title;

        //大标题合并居中
        $objPHPExcel->setActiveSheetIndex(0)->getRowDimension('1')->setRowHeight(30);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:K1');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setHorizontal('center');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $titleBig);

        $columnsRow = 2;
        //标题
        for($i=0;$i<count($columns);$i++){
            //设置宽度
            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($cellName[$i])->setWidth(20);
            //设置标题
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].$columnsRow, $columns[$i]);
        }
        $objPHPExcel->setActiveSheetIndex(0)->getRowDimension('2')->setRowHeight(20);

        //数据开始
        $row = 3;
        foreach ($param as $key=>$value) {

            $value = array_values($value);
            for($i=0;$i<count($value);$i++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$i].$row,$value[$i]);

            }
            $row++;
        }
        //加边框
        $objPHPExcel->setActiveSheetIndex(0)->getStyle(
            'A1:' .
            $objPHPExcel->setActiveSheetIndex(0)->getHighestColumn() .
            $objPHPExcel->setActiveSheetIndex(0)->getHighestRow()
        )->getBorders()->getAllBorders()->setBorderStyle('thin');


        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$fileName.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output');

        exit;
    }

    public function onlineWarn($companyId = 'null',$vehicleNo = 'null',$deviceNo = 'null'){
        $param['pageNo']      = 1;
        $param['pageSize']    = 100;
        $param['companyId']   = $companyId!='null'?$companyId:$this->context->loginuser->company_id;
        $model = D('Vehicle');
        $res = $model->getList($param);
        if ($res['code'] == Code::OK && $res['data']['totalRecord'] > 0) {
            $exportData = array();
            foreach ($res['data']['dataList'] as $key=>$value) {

                $offlineLast             = time() - $value['position_info']['gps_time'];
                //判断是否离线
                if($offlineLast > 60*10) {
                    $tmpData['time']         = date('Y-m-d H:i:s');
                    $tmpData['companyName']  = $value['company_name'];
                    $tmpData['carNo']        = $value['vehicle_no'];
                    $tmpData['deviceNo']     = $value['device_no'];
                    $offlineTime             = date('Y-m-d H:i:s',$value['position_info']['gps_time']);

                    $tmpData['offlineTime']  = $offlineTime;
                    $tmpData['offlineLast']  = round($offlineLast/(60*60*24),2);
                    $lng                     = $value['position_info']['lng'];
                    $lat                     = $value['position_info']['lat'];
                    $tmpData['location']     = !empty($lng) && !empty($lat) ? $this->getMapLocation($lng, $lat) : '未知';
                    $tmpData['lng']          = $lng;
                    $tmpData['lat']          = $lat;
                    $tmpData['handleMethod'] = '';
                    $tmpData['handleResult'] = '';
                    $tmpData['handleUser']   = '';
                    $exportData[]            = $tmpData;
                }

            }
            $filename = '离线台账'.date('Y-m-d H/i/s');
            $title    = '离线台账';
            $columns  = array('排查时间','车辆单位','车牌号','设备号','离线时间','离线天数','离线位置','经度','纬度','处置措施','处理结果','操作员');

            $this->exportExcel($filename,$title,$columns,$exportData);

        } else {

        }
    }
}