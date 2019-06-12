<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 11:42
 */

namespace Office\Controller;

use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Logger\Logger;
use Lib\Msg;
use Lib\StatusCode;
use Lib\Ws\WsClient;

class DriverController extends ListManagementController
{
    public function index(){
        die('禁止访问');
    }

    /**
     * 司机添加页面
     * author 李文起
     */
    public function add(){

        $this->breadcrumb = array("司机管理"=>"/","添加司机"=>'/');

        $this->addJS("office/driver/add.js");
        $this->display();
    }

    /**
     * 添加司机
     * author 李文起
     */
    public function ajaxAddDriver(){

        $data['name']                            = I('post.name');
        $data['sex']                             = I('post.sex');
        $data['account']                         = I('post.account');
        $data['phone']                           = I('post.phone');
        $data['password']                        = I('post.password');
        $data['certification_code']             = I('post.certification_code');
        $data['certification_expire_time']      = strtotime(I('post.certification_expire_time'));
        $data['driving_age']                      = I('post.driving_age');
        $data['status']                            = I('post.status');
        $data['code']                              = I('post.phone_code');
        $data['company_id']                       = $this->context->loginuser->company_id;

        $model = D('Driver');
        $res = $model->addDriver($data);

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code'],'data'=>$res['data']));
    }

    /**
     * 司机列表
     * author 李文起
     */
    public function lists(){
        $this->breadcrumb = array("司机管理"=>"/","司机列表"=>'/');


        // 列表检索条件
        $session_para = $this->getSessionParam('Driver');

        if (false !== $session_para) {

            if ($session_para['phone'] != 'null' && !empty($session_para['phone'])) {
                $this->assign('where_phone', $session_para['phone']);
            } else {
                $this->assign('where_phone', '');
            }

            if ($session_para['account'] != 'null' && !empty($session_para['account'])) {
                $this->assign('where_account', $session_para['account']);
            } else {
                $this->assign('where_account', '');
            }

            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }

            if ($session_para['status'] != 'null' && !empty($session_para['status'])) {
                $this->assign('where_status', $session_para['status']);
            } else {
                $this->assign('where_status', '');
            }
        }

        $this->addJS("office/driver/lists.js");
        $this->display();
    }

    /**
     * 司机列表
     * author 李文起
     */
    public function ajaxLists(){
        $para = array('name'=>'string','status'=>'int','phone'=>'string','companyId'=>'string','account'=>'string');
        $_GET['companyId'] = $this->context->loginuser->company_id;
        $this->setFilterCondition($para);
        $list = $this->getList('Driver','driverLists');
        $this->ajaxReturn($list,'json');
    }

    /**
     * 司机列表
     * author 李文起
     * @param $list
     * @return mixed
     */
    public function driverLists($list){

        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action = '<div class="layui-table-cell laytable-cell-1-12">';
                $value->action .= '<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="detail">查看</a>';
                if ($value->ws_open_id == null){
                    $value->action .= '<a class="layui-btn layui-btn-xs" lay-event="edit">开通微视</a>';
                } else {
                    $value->action .= '<a class="layui-btn layui-btn-xs" lay-event="monitor">监控</a>';
                }
                if ($value->face_path) {
                    $value->action .= '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="face">更新人脸照片</a>';
                } else {
                    $value->action .= '<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="face">上传人脸照片</a>';
                }

                $value->action .= '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>';

                $value->action .= '</div>';
            }
        }
        return $list;
    }

    /**
     * 修改司机信息
     * author 李文起
     */
    public function ajaxUpdateDriver(){
        $data['id']                              = I('post.id');
        $data['name']                            = I('post.name');
        $data['sex']                             = I('post.sex');
        $data['account']                         = I('post.account');
        $data['phone']                           = I('post.phone');
        $data['password']                        = I('post.password');
        $data['certification_code']             = I('post.certification_code');
        $data['certification_expire_time']      = strtotime(I('post.certification_expire_time'));
        $data['driving_age']                     = I('post.driving_age');
        $data['status']                           = I('post.status');
        $data['company_id']                       = $this->context->loginuser->company_id;

        $model = D('Driver');
        $res = $model->updateDriver($data);

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));

    }

    /**
     * 删除司机
     * author 李文起
     */
    public function ajaxDeleteDriver(){

        $data['id']              = I('post.id');
        $data['company_id']     = $this->context->loginuser->company_id;
        $data['del_flg']        = CommonConst::DEL_FLG_DELETED;

        $model = D('Driver');
        $res = $model->deleteDriver($data);
        if ($res['code'] == Code::OK) {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>Msg::MSG_DEL_SUCCESS,'status_code'=>$res['status_code']));
        } else {
            $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
        }
    }

    /**
     * 开通微视
     * author 李文起
     * @param $id
     */
    public function openWs($id){

        //获取司机信息
        $model = D('Driver');
        $res = $model->getDriverInfo($id,$this->context->loginuser->company_id);

        $this->breadcrumb = array("司机管理"=>"/","开通微视账号"=>'/');

        $this->addJS("office/driver/openWs.js");
        $this->assign('driverInfo',$res['data']);
        $this->display();
    }

    /**
     * 发送手机号验证码
     * author 李文起
     * @param $phone
     */
    public function ajaxSendCode($phone){
        $model = D('Driver');
        $res = $model->sendRegisterCode($phone);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));
    }

    /**
     * 注册微视账号
     * author 李文起
     */
    public function wsUserRegister(){
        $data['name']        = I('post.name');
        $data['account']    = I('post.account');
        $data['phone']      = I('post.phone');
        $data['password']   = I('post.password');
        $data['code']       = I('post.phone_code');

        $wsClient = WsClient::getInstance();
        $res = $wsClient->userRegister($data['name'],$data['phone'],$data['account'],$data['password'],$data['code']);

        if ($res['code'] == CommonConst::API_CODE_SUCCESS) {

            //更新本地数据
            $model = D('Driver');

            $data['id']             = I('post.id');
            $data['company_id']     = $this->context->loginuser->company_id;
            $data['ws_open_id']     = $res['data']['open_id'];

            $result = $model->updateDriver($data);
            if ($result['code'] == CommonConst::API_CODE_ERROR) {
                Logger::error('司机[ID:'.$data['id'].']更新数据失败');
            }
        }

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code']));

    }

    /**
     * 司机监控列表
     * author 李文起
     */
    public function behaviorLists(){
        $this->breadcrumb = array("司机管理"=>'/', "监控列表"=>"#");
        $this->addCSS(array('office/driver/userBehavior.css'=>'all'));
        $this->addJS(array('office/driver/behaviorLists.js'));
        $this->addAllJS('http://api.map.baidu.com/api?v=2.0&ak=mbxCCTHApgXL9heLp0RMxOoY');
        $this->addAllJS('https://g.alicdn.com/de/prismplayer/2.8.0/aliplayer-min.js');

        $this->assign('where_deviceNo',I('get.deviceNo'));
        $this->assign('where_vehicleNo',I('get.vehicleNo'));

        $this->display();
    }

    /**
     * 司机监控列表
     * author 李文起
     */
    public function ajaxGetBehaviorLists(){

        $param['startTime']     = I('post.startTime') == 'null' ? 'null' : strtotime(I('post.startTime'));
        $param['endTime']       = I('post.endTime') == 'null' ? 'null' : strtotime(I('post.endTime'));
        $param['pageNo']        = I('post.pageNo');
        $param['pageSize']      = I('post.pageSize');
        $param['name']          = I('post.name');
        $param['phone']         = I('post.phone');
        $param['code']          = I('post.code');
        $param['vehicleNo']     = I('post.vehicleNo') == "null" ? '':I('post.vehicleNo');
        $param['deviceNo']      = I('post.deviceNo')== "null" ? '':I('post.deviceNo');

        $deviceNo    = I('post.deviceNo');
        $vehicleNo   = I('post.vehicleNo');
        $this->setState('behavior_list_where',$param);

        $model = D('DrivingMonitor');
        $res = $model->behaviorLists($this->context->loginuser->company_id,$param['pageNo'],$param['pageSize'], $param['startTime'],$param['endTime'],$param['name'],$param['phone'],$param['code'],$deviceNo,$vehicleNo);

        if ($res['code'] == Code::OK && $res['data']['totalRecord'] > 0) {
            foreach ($res['data']['dataList'] as $key=>&$value) {
                //车辆
                $value['vehicle_no'] = empty($value['vehicle_no']) ? '未知车辆' : $value['vehicle_no'];
                // 车速,原始速度为m/s，转换为km/h
                $value['kmh'] = $value['speed'] ? $value['speed']*3.6 : 0;
                // 时间
                $value['location_time'] = date('Y-m-d H:i:s', $value['location_time']);
                // 图片
                $value['path'] = !empty($value['path']) ? getOssFileUrl($value['path'], 'img') : '';
                //视频
                $value['video_path'] = !empty($value['video_path']) ? getOssFileUrl($value['video_path'], 'video') : '';
                // 位置
                $lng = $value['location_lng'];
                $lat = $value['location_lat'];
                $value['location'] = !empty($lng) &&($lng > 1)&& !empty($lat)&&($lat >1) ? $this->getMapLocation($lng, $lat,'baidu') : '未知';
                $value['location_lng_bd'] = $value['location_lng'];
                $value['location_lat_bd'] = $value['location_lat'];
            }
        }

        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code'],'data'=>$res['data']));
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
     * 司机行为数据
     * @param $id
     */
    public function monitor($id)
    {
        $this->breadcrumb = array("首页"=>'/', "司机管理"=>"#", "司机监控"=>"#");
        $this->addJS(array('office/monitoring/echarts.js','office/monitoring/westeros.js','office/monitoring/bmap.js','office/driver/monitor.js'));
        $model = D('Driver');
        $driverInfo = $model->getDriverInfo($id,$this->context->loginuser->company_id);
        $this->assign('driverInfo',$driverInfo['data']);
        $this->assign('driverId',$id);
        $this->display();
    }

    /**
     * 疲劳类型
     * @param $driverId
     */
    public function driverTiredType($driverId)
    {
        $model = D('DrivingMonitor');
        $timeType = I('get.timeType');
        if ($timeType == 30) {
            $startTime = time()-3600*30*24;
        } else if ($timeType == 7) {
            $startTime = time()-3600*30*7;
        } else {
            $startTime = time()-3600*30*3;
        }
        $endTime = time();
        $res = $model->driverTiredType($startTime,$endTime,$this->context->loginuser->company_id,$driverId);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 报警次数
     * @param $driverId
     */
    public function driverTiredNumber($driverId)
    {

        $endTime   = time();
        $timeType = I('post.timeType');
        if ($timeType == 30) {
            $startTime = time()-3600*30*24;
        } else if ($timeType == 7) {
            $startTime = time()-3600*30*7;
        } else {
            $startTime = time()-3600*30*3;
        }
        $model = D('DrivingMonitor');
        $res = $model->driverTiredNumber($startTime,$endTime,$this->context->loginuser->company_id,$driverId);
        if ($res['code'] == Code::OK) {
            $data = [];
            $data['xAxis'] = array();
            $data['yAxis'] = array();
            foreach ($res['data'] as $k=>$v) {
                array_push($data['xAxis'],$k);
                array_push($data['yAxis'],$v);
            }
            $this->ajaxReturn(array('code'=>Code::OK,'msg'=>$res['msg'],'data'=>$data));
        } else  {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>$res['msg']));
        }
    }

    /**
     * 报警集中时间段
     * @param $driverId
     */
    public function driverTiredByTimeGroup($driverId)
    {
        $timeType = I('get.timeType');
        if ($timeType == 30) {
            $startTime = strtotime(date("Y-m-d",(time()-3600*24*30)));
        } else if ($timeType == 7) {
            $startTime = strtotime(date("Y-m-d",(time()-3600*24*7)));
        } else {
            $startTime = strtotime(date("Y-m-d",(time()-3600*24*3)));
        }
        $endTime   = strtotime(date("Y-m-d",time()))+3600*24;
        $model = D('Driver');
        $res = $model->statByTimeGroup($this->context->loginuser->company_id,$startTime,$endTime,$driverId);

        if ($res['code'] == Code::OK) {

            $this->ajaxReturn(array('code'=>Code::OK,'msg'=>$res['msg'],'data'=>$res['data']));
        } else  {
            $this->ajaxReturn(array('code'=>Code::ERROR,'msg'=>$res['msg']));
        }
    }

    /**
     * 疲劳值
     * @param $driverId
     */
    public function driverTiredValue($driverId)
    {
        $model = D('Driver');
        //$driverInfo = $model->getDriverInfo($driverId,$this->context->loginuser->company_id);
        $res = $model->driverTiredValue($driverId);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 报警分类
     * @param $driverId
     */
    public function driverTiredNoByType($driverId)
    {
        $model = D('Driver');
        $timeType = I('post.timeType');
        if ($timeType == 30) {
            $time = time()-3600*30*24;
        } else if ($timeType == 7) {
            $time = time()-3600*30*7;
        } else {
            $time = time()-3600*30*3;
        }
        $res = $model->driverTiredNoByType($this->context->loginuser->company_id,$time,$driverId);
        $this->ajaxReturn(array('code' => $res['code'], 'msg' => $res['msg'], 'data' => $res['data']));
    }

    /**
     * 上传人脸
     * author 李文起
     */
    public function uploadFace(){
        $img = file_get_contents($_FILES['file']['tmp_name']);

        if (isset($img)) {
            $img = base64_encode($img);

            $data['img']         = $img;
            $data['fileType']   = substr($_FILES['file']['name'],-3);
            $data['driverId']   = I('post.driverId');

            $model = D('Driver');
            $res = $model->uploadFace($data);
            $result = array('code' => $res['code'], 'msg' => $res['msg'], 'status_code' => $res['status_code'],'data'=>$res['data']);
        } else {
            $result = array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING,'status_code'=>StatusCode::PARA_MISSING);
        }
        $this->ajaxReturn($result);
    }

    /**
     * 导出行为图片
     * author 李文起
     */
    public function exportImagesView(){
        $this->breadcrumb = array("司机管理"=>'/', "导出行为图片"=>"#");
        $this->addJS(array('office/driver/exportImagesView.js'));
        $this->display();
    }

    /**
     * 导出行为图片
     * author 李文起
     */
    public function exportBehaviorImages(){
        $param['startTime']     = I('post.startTime') == 'null' ? 'null' : strtotime(I('post.startTime'));
        $param['endTime']       = I('post.endTime') == 'null' ? 'null' : strtotime(I('post.endTime'));
        $param['deviceNos']     = I('post.deviceNos') == 'null' ? 'null' : I('post.deviceNos');
        $param['code']          = I('post.code') == 'null' ? 'null' : I('post.code');
        $param['imgType']       = I('post.imgType');

        if (is_numeric($param['startTime']) && is_numeric($param['endTime']) && isset($param['deviceNos']) && isset($param['code']) && isset($param['imgType'])) {

            $param['zip']          = date('Ymd_His')."_code.zip";
            $param['companyId']    = $this->context->loginuser->company_id;
            $param['token']        = $this->context->loginuser->token;
            //$model = D('DrivingMonitor');
            //$res = $model->exportBehaviorImages($this->context->loginuser->company_id,$param['startTime'],$param['endTime'],$param['deviceNos'],$param['code']);


            $httpCode = httpSyncPost(C('API_SERVER').'/DrivingMonitor/exportBehaviorImages/token/'.$this->context->loginuser->token,$param);

            $result = array('code'=>Code::OK,'msg'=>Msg::GET_DATA_SUCCESS,'status_code'=>StatusCode::GET_DATA_SUCCESS,'data'=>$param);
        } else {
            $result = array('code'=>Code::ERROR,'msg'=>Msg::PARA_MISSING,'status_code'=>StatusCode::PARA_MISSING);
        }
        $this->ajaxReturn($result);

    }

    /**
     * 查看下载标记位
     * author 李文起
     */
    public function checkPullAll(){
        $model = D('DrivingMonitor');
        $res = $model->checkPullAll();
        $result = array('code'=>$res['code'],'msg'=>$res['msg'],'status_code'=>$res['status_code'],'data'=>$res['data']);
        $this->ajaxReturn($result);
    }

    /**
     * 下载压缩包
     * author 李文起
     * @param $fileName
     */
    public function downloadFile($fileName){

        set_time_limit ( 0 );
        ini_set("memory_limit","-1");

        $fileZip = C('DOWNLOAD_FILE_PATH').$fileName;

        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . filesize($fileZip));
        header("Content-Disposition: attachment; filename=\"" . basename($fileZip) . "\"");
        readfile($fileZip);

        $fp=fopen($fileZip,"r");
        $filesize=filesize($fileZip);
        header("Content-type:application/zip");
        header("Accept-Ranges:bytes");
        header("Accept-Length:".$filesize);
        $buffer=1024;
        $buffer_count=0;
        while(!feof($fp)&&$filesize-$buffer_count>0){
            $data=fread($fp,$buffer);
            $buffer_count+=$buffer;
            echo $data;
        }

        fclose($fp);

        //删除压缩包
        unlink($fileZip);

        exit('已生成');
    }
}