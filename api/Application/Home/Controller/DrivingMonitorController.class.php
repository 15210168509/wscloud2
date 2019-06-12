<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/21
 * Time: 10:56
 */

namespace Home\Controller;


use Lib\AiConst\BehaviorConst;
use Lib\Auth;
use Lib\Code;
use Lib\CommonConst;
use Lib\Msg;
use Lib\RedisLock;
use Lib\Status;
use Lib\StatusCode;
use Lib\Tools;
use Lib\Ws\WsClient;
use Lib\GpsConvert;

class DrivingMonitorController extends AdvancedRestController
{


    /**
     * author 李文起
     * @param $companyId
     * @param $pageNo
     * @param $pageSize
     * @param $startTime
     * @param $endTime
     * @param $name
     * @param $phone
     * @param $code
     * @param $deviceNo
     * @param $vehicleNo
     */
    public function getBehaviorLists($companyId='null',$pageNo,$pageSize,$startTime = 'null',$endTime = 'null',$name = 'null',$phone = 'null',$code = 'null', $deviceNo = 'null',$vehicleNo = 'null'){
        if (is_numeric($pageNo) && is_numeric($pageSize)) {

            $wsClient = WsClient::getInstance();
            $res = $wsClient->getBehaviorList($companyId,$pageNo,$pageSize,$startTime,$endTime,$name,$phone,$code,$deviceNo,$vehicleNo);

            if ($res['code'] == Code::OK) {
                foreach ($res['data']['dataList'] as $key=>&$value) {
                    $value['name']         = $value['driver_name']?$value['driver_name']:'未知姓名';
                    $value['type_text']    = BehaviorConst::behaviorTypeStr($value['type']);
                    $value['level_text']   = BehaviorConst::behaviorLevelStr($value['level']);
                    $value['code_text']    = BehaviorConst::behaviorTypeCodeStr($value['code']);
                    $bdPosition            = GpsConvert::wgs84ToBd09($value['location_lng'],$value['location_lat']);
                    $value['location_lng_org'] = $value['location_lng'];
                    $value['location_lat_org'] = $value['location_lat'];
                    $value['location_lng'] = $bdPosition[0];
                    $value['location_lat'] = $bdPosition[1];
                }
            }

            $this->setReturnVal($res['code'], $res['msg'],$res['status_code'],$res['data']);


        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 导出行为图片
     * author 李文起
     */

    public function exportBehaviorImages(){
        $redis = RedisLock::getInstance();
        $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_DATA_START,'msg'=>'查询数据中...')),0);

        $companyId =  $_POST['companyId'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        $deviceNos=$_POST['deviceNos'];
        $code=$_POST['code'];


        if (isset($companyId) && is_numeric($startTime) && is_numeric($endTime)) {
            $wsClient = WsClient::getInstance();
            $res = $wsClient->exportBehaviorImages($companyId,$startTime,$endTime,$deviceNos,$code);
            if ($res['code'] == Code::OK) {

                $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_DATA_SUCCESS,'msg'=>'数据查询成功。')),0);


                if (count($res['data']['dataList']) > 0) {

                    $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_DOWNLOAD_START,'msg'=>'开始压缩文件，请稍等...')),0);
                    $this->exportImage($res['data']['dataList'],$_POST['imgType'],$_POST['zip']);
                    $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_DOWNLOAD_SUCCESS,'msg'=>'压缩完成。')),0);

                } else {

                    $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_BEHAVIOR_EMPTY,'msg'=>'行为列表为空。')),0);

                }

            } else {
                $redis->set('safe_downloadFileFlg',json_encode(array('flg'=>BehaviorConst::SELECT_DATA_ERROR,'msg'=>'数据查询失败。')),0);
            }
        }
    }

    /**
     * 检测
     * author 李文起
     */
    public function checkPullAll(){
        $redis = RedisLock::getInstance();
        $res = $redis->get('safe_downloadFileFlg');
        $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
        $this->restReturn();
    }

    /**
     * author 李文起
     * @param $dataList
     * @param $type
     * @param $fileZip
     */
    private function exportImage($dataList,$type,$fileZip){

        //创建临时文件夹
        $dir = iconv("UTF-8", "GBK", "Download/");
        if (!file_exists($dir)){
            mkdir ($dir,0777,true);
        }

        $zip = new \ZipArchive();
        $zip->open($fileZip,\ZipArchive::CREATE);   //打开压缩包*/

        foreach ($dataList as $value) {

            //疲劳类型
            $value['code_text']  = BehaviorConst::behaviorTypeCodeExportImage($value['code']);

            $extraInfo = json_decode($value['extra_info'],true);
            $url = getOssFileUrl($value['path'], $type);
            $postfix = '.jpg';
            switch ($type) {
                case 'img': {
                    $postfix = '.jpg';
                    break;
                }
                case 'nv21':{
                    $postfix = '.nv21';
                    $urlArray = explode('.jpg',$url);
                    $url = $urlArray[0].$postfix;
                    break;
                }
                default : break;
            }

            $filename = 'Download/'.$value['code_text'].'_(pitch_'.$extraInfo['pitch'].'_pitchCalibration_'.$extraInfo['pitchCalibration'].')_'.date("YmdHis",$value['location_time']).$postfix;//文件名称生成

            ob_start();//打开输出
            readfile($url);//输出图片文件
            $img = ob_get_contents();//得到浏览器输出
            ob_end_clean();//清除输出并关闭
            $size = strlen($img);//得到图片大小

            $myFile = fopen(iconv ( 'UTF-8', 'GBK', $filename ), "w");
            fwrite($myFile,$img,$size);
            fclose($myFile);

            $zip->addFile(iconv ( 'UTF-8', 'GBK', $filename ));   //向压缩包中添加文件
        }

        $zip->close();  //关闭压缩包

        //删除临时文件
        $this->rm_empty_dir('Download/');

    }


    /**
     * 删除非空文件夹
     * author 李文起
     * @param $directory
     */
    private function rm_empty_dir($directory){
        if(file_exists($directory)){//判断目录是否存在，如果不存在rmdir()函数会出错
            if($dir_handle=opendir($directory)){//打开目录返回目录资源，并判断是否成功
                while($filename=readdir($dir_handle)){//遍历目录，读出目录中的文件或文件夹
                    if($filename!='.' && $filename!='..'){//一定要排除两个特殊的目录
                        $subFile=$directory."/".$filename;//将目录下的文件与当前目录相连
                        if(is_dir($subFile)){//如果是目录条件则成了delDir($subFile);//递归调用自己删除子目录
                            $this->rm_empty_dir($subFile);
                        }
                        if(is_file($subFile)){//如果是文件条件则成立unlink($subFile);//直接删除这个文件
                            unlink($subFile);
                        }
                    }else {
                        rmdir($directory);//删除空目录
                    }
                }
                closedir($dir_handle);//关闭目录资源
                rmdir($directory);//删除空目录
            }
        }
    }

    /**
     * 统计30天内的报警次数
     * author 李文起
     * @param $companyId
     * @param string $startTime
     * @param string $endTime
     */
    public function statBehavior($companyId,$startTime = 'null',$endTime = 'null'){
        if (is_numeric($companyId)) {

            $model = D('DrivingMonitor');

            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    = array(array('EGT',$startTime),array('LT',$endTime));

            $result = $model
                ->field('count(1) count,code')
                ->where($map)
                ->group('code')
                ->order('count desc')
                ->limit(6)
                ->select();

            if (false !== $result) {

                foreach ($result as &$value){
                    $value['code_text'] = BehaviorConst::behaviorTypeCodeStr($value['code']);
                }

                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $result);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 按天分组
     * author 李文起
     * @param $startTime
     * @param $endTime
     * @return array
     */
    private function daysCondition($startTime,$endTime) {
        $days = array();

        while ($startTime <= $endTime){
            $startDay = date('Y-m-d',$startTime);
            $days[$startDay] = 0;
            $startTime = strtotime($startDay .' +1 day');
        }
        return $days;
    }

    /**
     * 按天分组统计
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     */
    public function statByDayGroup($companyId,$startTime,$endTime,$driverId='null'){

        if (is_numeric($companyId) && is_numeric($startTime) && is_numeric($endTime)) {

            $model = D('DrivingMonitor');

            if (isset($driverId) && $driverId != 'null') {
                $map['driver_id'] = $driverId;
            }
            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    = array(array('EGT',$startTime),array('LT',$endTime));

            $daysArray = $this->daysCondition($startTime, $endTime);

            $result = $model
                ->field(array('FROM_UNIXTIME(create_time, "%Y-%m-%d") time', 'count(1) count'))
                ->where($map)
                ->group('time')
                ->select();

            if (false !== $result) {
                foreach ($result as $key=>$value) {
                    if (array_key_exists($value['time'], $daysArray)) {
                        $daysArray[$value['time']] = intval($value['count']);
                    }
                }
                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $daysArray);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 行为分类统计
     * @param $startTime
     * @param $endTime
     * @param $companyId
     * @param $driverId
     */
    public function showTiredType($startTime,$endTime,$companyId,$driverId='null')
    {
        $model = D('DrivingMonitor');
        $map['del_flg'] = CommonConst::DEL_FLG_OK;
        $map['create_time'] = array(array('gt',$startTime),array('lt',$endTime)) ;
        $map['company_id'] = $companyId;
        if (isset($driverId) && $driverId != 'null') {
            $map['driver_id'] = $driverId;
        }
        $res = $model->field('code,count(code) value')->where($map)->group('code')->select();
        if ($res) {
            foreach ($res as &$val) {
                $val['name'] = BehaviorConst::behaviorTypeCodeStr($val['code']);
            }
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$res);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }

    /**
     * 按小时分
     * author 李文起
     * @return mixed
     */
    private function timeCondition(){
        $time = array();
        $h = 0;
        while ($h < 24){
            if ($h<10){
                $time['0'.$h] = 0;
            } else {
                $time[$h] = 0;
            }
            $h++;
        }
        return $time;
    }
    private function timeArray(){
        $time = array();
        $h = 0;
        while ($h < 24){
            $time[$h] = 0;
            $h++;
        }
        return $time;
    }

    /**
     * 按小时分组
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     */
    public function statByTimeGroup($companyId,$startTime,$endTime,$driverId='null'){
        if (is_numeric($companyId) && is_numeric($startTime) && is_numeric($endTime)) {

            $model = D('DrivingMonitor');

            if (isset($driverId) && $driverId != 'null') {
                $map['driver_id'] = $driverId;
            }
            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    = array(array('EGT',$startTime),array('LT',$endTime));


            $result = $model
                ->field(array('FROM_UNIXTIME(create_time, "%H") time', 'count(1) count','code'))
                ->where($map)
                ->group('code ,time')
                ->select();
            $data = [];
            $line = [];
            if (false !== $result) {
                foreach ($result as $key=>$value) {
                    $data[$value['code']][intval($value['time'])] = $value['count'];
                }

                foreach ($data as $key=>$val) {
                    $lineData = $this->timeArray();
                    foreach ($val as $k=>$v) {
                        $lineData[$k] = $v;
                    };
                    $line[] = array('name'=>BehaviorConst::behaviorTypeCodeStr($key),'type'=>'line','smooth'=>true,'data'=>$lineData);
                }

                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $line);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 按小时分析疲劳指数
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param string $driverId
     */
    public function statDriverTimeTiredValue($companyId,$startTime,$endTime,$driverId = 'null'){
        if (is_numeric($companyId) && is_numeric($startTime) && is_numeric($endTime)) {

            $model = D('DrivingMonitor');

            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']   = array(array('EGT',$startTime),array('LT',$endTime));
            if ($driverId != 'null'){
                $map['driver_id']  = $driverId;
            }

            $result = $model
                ->field(array('FROM_UNIXTIME(create_time, "%H") time', 'AVG(tired_value) tired_value'))
                ->where($map)
                ->group('FROM_UNIXTIME(create_time, "%H")')
                ->select();

            $timeArray = $this->timeCondition();

            if (false !== $result) {
                foreach ($result as $key=>$value) {
                    if (array_key_exists($value['time'], $timeArray)) {
                        $timeArray[$value['time']] = intval($value['tired_value']);
                    }
                }
                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $timeArray);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }

        $this->restReturn();
    }

    /**
     * 按天进行疲劳分析
     * author 李文起
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param string $driverId
     */
    public function statDriverDayTiredValue($companyId,$startTime,$endTime,$driverId = 'null'){
        if (is_numeric($companyId) && is_numeric($startTime) && is_numeric($endTime)) {

            $model = D('DrivingMonitor');

            if (isset($driverId) && $driverId != 'null') {
                $map['driver_id'] = $driverId;
            }
            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    = array(array('EGT',$startTime),array('LT',$endTime));

            $daysArray = $this->daysCondition($startTime, $endTime);

            $result = $model
                ->field(array('FROM_UNIXTIME(create_time, "%Y-%m-%d") time', 'AVG(tired_value) tired_value'))
                ->where($map)
                ->group('time')
                ->select();

            if (false !== $result) {
                foreach ($result as $key=>$value) {
                    if (array_key_exists($value['time'], $daysArray)) {
                        $daysArray[$value['time']] = intval($value['tired_value']);
                    }
                }
                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS, $daysArray);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 疲劳值
     */
    public function statTiredValue()
    {
        if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
            $resData = array();
            $wsClient = WsClient::getInstance();
            foreach ($pare['device_no'] as $val) {
                $deviceNoArray = explode('_',$val);
                $res = $wsClient->getTiredValueBySafePlatform($deviceNoArray[0],strtotime(date('Y-m-d H:i:s',time()).' -1 days'),time(),30);
                $data = [];
                if ($res != false && !empty($res['data'])) {

                    for ($i = count($res['data']) -1; $i>= 0; $i--) {
                        $data['data'][] = $res['data'][$i]['tired_value'];
                    }
                    $data['name'] =  $deviceNoArray[1];
                    $data['type'] = 'line';
                    $data['smooth'] = true;
                    $data['symbol'] = 'none';
                } else {
                    $data['data'] = ['0','0','0','0','0'];
                    $data['name'] =  $deviceNoArray[1];
                    $data['type'] = 'line';
                    $data['smooth'] = true;
                }
                $resData[] = $data;

            }
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$resData);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 报警提醒（行为类型分类）
     * @param $companyId
     * @param $startTime
     * @param string $driverId
     */
    public function statTiredNo($companyId,$startTime,$driverId='null'){

        if (is_numeric($companyId) && is_numeric($startTime)) {

            $model = D('DrivingMonitor');

            if (isset($driverId) && $driverId != 'null') {
                $map['driver_id'] = $driverId;
            }
            $map['del_flg']       = CommonConst::DEL_FLG_OK;
            $map['company_id']    = $companyId;
            $map['create_time']    =array('EGT',$startTime);

            $resData = array();
            $resData['x'] = [];
            $resData['y'] = [];
            $result = $model
                ->field(array('code', 'count(code) count'))
                ->where($map)
                ->group('code')
                ->select();
            $sum = $model->where($map)->count();
            if (false !== $result) {
                $resData['sum'] = num2str($sum,7);
                foreach ($result as $value) {
                    $resData['x'][] = BehaviorConst::behaviorTypeCodeStr($value['code']);
                    $resData['y'][] = $value['count'];
                }
                $this->setReturnVal(Code::OK, Msg::GET_DATA_SUCCESS, StatusCode::GET_DATA_SUCCESS,$resData);
            } else {
                $this->setReturnVal(Code::ERROR, Msg::GET_DATA_ERROR, StatusCode::GET_DATA_ERROR);
            }

        }  else {
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }

    /**
     * 获取车辆位置
     * author 李文起
     */
    public function getVehiclePosition(){

        $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);

        if (count($pare) > 0) {
            $deviceNoArray = array();
            foreach ($pare as $val) {
                $deviceNoArray[] = explode('_',$val)[0];
            }
            $model = D('vehicle');
            $map['del_flg']     = CommonConst::DEL_FLG_OK;
            $map['device_no']   = array('IN',implode(',',$deviceNoArray));
            $res = $model->where($map)->select();
            $redis = RedisLock::getInstance();
            foreach ($res as $key=>&$value ) {
                $gps84Location = $redis->get($value['device_no']);
                $bd_location   = GpsConvert::wgs84ToBd09($gps84Location['lng'],$gps84Location['lat']);
                $value['position_info'] = $bd_location;
            }
            $this->setReturnVal(Code::OK,Msg::GET_DATA_SUCCESS,StatusCode::GET_DATA_SUCCESS,$res);
        } else{
            $this->setReturnVal(Code::ERROR,Msg::PARA_MISSING,StatusCode::PARA_MISSING);
        }
        $this->restReturn();
    }


    /**
     * 司机疲劳值
     */
    public function driverTiredValue()
    {
        $pare = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        $model = D('DrivingMonitor');
        $map['driver_id'] = $pare['driverId'];

        $res = $model
            ->field('tired_value,location_time')
            ->where($map)
            ->order('location_time desc')
            ->limit(30)
            ->select();

        if ($res != false) {
            $res = array_reverse($res);
            $data = [];
            foreach ($res as $value) {
                $data['y'][] = $value['tired_value'];
                $data['x'][] = date('Y-m-d H:i:s',$value['location_time']);
            }
            $this->setReturnVal(Code::OK,Msg::OK,StatusCode::OK,$data);
        } else {
            $this->setReturnVal(Code::ERROR,Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();

    }
}