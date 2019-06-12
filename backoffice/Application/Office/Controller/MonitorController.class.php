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
use Lib\Tools;

class MonitorController extends ListManagementController
{

    public $authentication = true;

    public function showData()
    {
        $this->breadcrumb = array("首页"=>'/', "监控中心"=>"#", "数据分析"=>"#");
        $this->addJS(array('office/monitoring/echarts.js','office/monitoring/westeros.js','office/monitoring/bmap.js','office/monitoring/showData.js'));
        $this->display();
    }

    public function realTimeData()
    {
        $this->breadcrumb = array("首页"=>'/', "监控中心"=>"#", "数据大屏"=>"#");
        $this->addJS(array('office/monitoring/echarts.js','office/monitoring/westeros.js','office/monitoring/bmap.js','office/monitoring/realTimeData.js','office/monitoring/digitalScroll.js','office/monitoring/gcoord.js'));
        $this->addAllJS('http://api.map.baidu.com/api?v=2.0&ak=mbxCCTHApgXL9heLp0RMxOoY');
        $this->addCSS(array('office/driver/monitor.css'=>'all'));
        $model = D('DrivingMonitor');
        $time = strtotime(date('Y-m-d',time()));
        $res = $model->statTiredNo($this->context->loginuser->company_id, $time);
        $this->assign('tiredWarningNumber',C('TIRED_WARNING_NUMBER'));
        $this->assign('sumTiredNo',$res['data']['sum']);
        $this->display();
    }


}