<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/6/6
 * Time: 10:32
 */

namespace Office\Controller;

use Lib\Code;
use Lib\CommonConst;
use Lib\ListManagementController;
use Lib\Msg;
use Lib\Status;
use Lib\Tools;

class CompanyController extends ListManagementController
{

    public $authentication = true;

    /**
     * 获取子公司列表
     */
    public function lists()
    {
        $this->breadcrumb = array("公司管理"=>'#','公司列表'=>'lists');
        $this->addJS(array('office/company/companyLists.js'));
        // 列表检索条件
        $session_para = $this->getSessionParam('Company');

        $this->assign('parentCompanyId',$this->context->loginuser->company_id);
        if (false !== $session_para) {
            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }
        }
        $this->display('companyList');
    }


    public function search()
    {
        $para = array('name'=>'string','parentCompanyId'=>'int');
        $this->setFilterCondition($para);
        $list = $this->getList('Company','formatList');
        $this->ajaxReturn($list,'json');
    }

    //列表处理回掉函数
    public function formatList($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $Key => &$value) {
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="package">套餐</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="info">详情</button>';
                $value->action .= '<button class="layui-btn layui-btn-xs layui-btn-danger del" lay-event="del">删除</button>';
                if ($value->verify_status == Status::COMPANY_VERIFY_STATUS_ING) {
                    $value->action .= '<button class="layui-btn layui-btn-xs" lay-event="verify">审核</button>';
                }
            }
        }
        return $list;
    }

    /**
     * 删除公司
     * @param $id
     */
    public function delCompany($id)
    {
        $model = D('Company');
        $res = $model->delCompany($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 审核公司
     */
    public function verifyCompany()
    {
        $data['id'] = I('get.id');
        $data['verify_status'] = I('get.verifyStatus');
        $data['comment'] = I('get.comment');
        $model = D('Company');
        $res = $model->verifyCompany($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 公司详情
     * @param $id
     */
    public function companyInfo($id)
    {
        $this->breadcrumb = array("公司管理"=>'#','公司详情'=>'#');
        $this->addJS(array('office/company/behaviorLists.js','office/company/companyInfo.js'));
        $this->addAllJS('http://api.map.baidu.com/api?v=2.0&ak=mbxCCTHApgXL9heLp0RMxOoY');
        $this->addAllJS('https://g.alicdn.com/de/prismplayer/2.8.0/aliplayer-min.js');
        $this->addCSS(array('office/index/index.css'=>'all'));
        $model = D('Company');
        $res = $model->getCompanyInfo($id);
        $this->assign('companyInfo',$res['data']);

        //报警监控数据
        $monitorRes = $model->statBehavior($id);
        $this->assign('monitor',$monitorRes['data']);

        //公司基本数据
        $res = $model->baseDataStats($id);
        $this->assign('baseData',$res['data']);

        //公司预警数据


        $this->addJS(array('admin/company/companyInfo.js'));
        $this->display();
    }

    /**
     * 司机监控列表
     * author 李文起
     */
    public function ajaxGetBehaviorLists(){

        $companyId              = I('post.companyId');
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
        $res = $model->behaviorLists($companyId,$param['pageNo'],$param['pageSize'], $param['startTime'],$param['endTime'],$param['name'],$param['phone'],$param['code'],$deviceNo,$vehicleNo);

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
                $value['location'] = !empty($lng) && !empty($lat) ? $this->getMapLocation($lng, $lat) : '未知';

                //坐标转换
                $point = $this->bd_encrypt($value['location_lng'],$value['location_lat']);
                $value['location_lng_bd'] = $point['bd_lon'];
                $value['location_lat_bd'] = $point['bd_lat'];
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
     * 设置套餐
     */
    public function setCompanyPackage()
    {
        $data['company_id'] = I('get.id');
        $data['start_time'] = strtotime(I('get.start_time'));
        $data['end_time'] = strtotime(I('get.end_time'));
        $data['money'] = I('get.money')*100;
        $data['devices'] = I('get.devices');
        $model = D('Company');
        $res = $model->setCompanyPackage($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

    /**
     * 公司套餐信息
     * @param $id
     */
    public function getPackageInfo($id)
    {
        $model = D('Company');
        $res = $model->getPackageInfo($id);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg'],'data'=>$res['data']));
    }

    /**
     * 添加公司
     */
    public function add()
    {
        $this->breadcrumb = array("公司管理"=>'#','添加公司'=>'add');
        $this->addJS('office/company/add.js');
        $this->display('add');
    }

    /**
     * 添加子公司
     */
    public function ajaxAddCompany()
    {
        $data['id'] = Tools::generateId();
        $data['name']       = I('post.name');
        $data['email']      = I('post.email');
        $data['phone']      = I('post.phone');
        $data['password']   = I('post.password');
        $data['roll']       = CommonConst::SUB_COMPANY;
        $data['parent_id']  =  $this->context->loginuser->company_id;
        $model = D('Company');
        $res = $model->ajaxAddCompany($data);
        $this->ajaxReturn(array('code'=>$res['code'],'msg'=>$res['msg']));
    }

}