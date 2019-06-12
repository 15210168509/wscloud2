<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/27
 * Time: 17:42
 */

namespace Admin\Controller;


use Lib\ListAdminController;

class BehaviorController extends ListAdminController
{
    /**
     * 预警列表
     * @param $companyId
     * @param $company
     */
    public function lists($companyId='null',$company='null')
    {
        $this->breadcrumb = array("预警管理"=>"/","预警列表"=>'/');


        // 列表检索条件
        $session_para = $this->getSessionParam('Behavior');

        if (false !== $session_para) {

            if ($session_para['startTime'] != 'null' && !empty($session_para['startTime']) && $session_para['endTime'] != 'null' && !empty($session_para['endTime'])) {
                $this->assign('where_time', date('Y-m-d',$session_para['startTime']).' - '.date('Y-m-d',$session_para['endTime']));
            } else {
                $this->assign('where_time', '');
            }

            if ($session_para['phone'] != 'null' && !empty($session_para['phone'])) {
                $this->assign('where_phone', $session_para['phone']);
            } else {
                $this->assign('where_phone', '');
            }

            if ($session_para['name'] != 'null' && !empty($session_para['name'])) {
                $this->assign('where_name', $session_para['name']);
            } else {
                $this->assign('where_name', '');
            }

            if ($session_para['companyId'] != 'null' && !empty($session_para['companyId'])) {
                $this->assign('where_companyId', $session_para['companyId']);
            } else {
                $this->assign('where_companyId',  $companyId == 'null'? '':$companyId);
            }

            if ($session_para['company'] != 'null' && !empty($session_para['company'])) {
                $this->assign('where_company', $session_para['company']);
            } else {
                $this->assign('where_company', $company == 'null' ?  '':$company);
            }

        }
        $this->addCSS(array('admin/behavior/userBehavior.css'=>'all'));
        $this->addJS("admin/behavior/lists.js");
        $this->display();
    }

    /**
     * 司机列表
     * author 李文起
     */
    public function ajaxLists(){
        $para = array('name'=>'string','phone'=>'string','startTime'=>'string','endTime'=>'string','companyId'=>'string','company'=>'string');
        $_GET['startTime'] = $_GET['startTime'] == 'null'? 'null': strtotime( $_GET['startTime']);
        $_GET['endTime'] = $_GET['endTime'] == 'null'? 'null': strtotime( $_GET['endTime']);
        $this->setFilterCondition($para);
        $list = $this->getList('Behavior','behaviorLists');
        $this->ajaxReturn($list,'json');
    }

    /**
     * 司机列表
     * author 李文起
     * @param $list
     * @return mixed
     */
    public function behaviorLists($list){
        if (is_array($list)&&count($list)>0) {
            foreach ($list as $key => &$value) {
                // 车速
                $value->kmh = $value->speed ?  $value->speed : 0;
                // 时间
                $value->location_time = date('Y-m-d H:i:s', $value->location_time);
                // 图片
                $value->path = !empty($value->path) ? getOssFileUrl($value->path, 'img') : '';
                // 位置
                $lng = $value->location_lng;
                $lat = $value->location_lat;
                $value->location = !empty($lng) && !empty($lat) ? $this->getMapLocation($lng, $lat) : '未知';
            }
        }
        return $list;
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
}