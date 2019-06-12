<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/26
 * Time: 11:48
 */

namespace Home\Controller;


use Lib\Code;
use Lib\CommonConst;
use Lib\Msg;
use Lib\Status;
use Think\Model;
use Lib\StatusCode;

class JtServerController extends AdvancedRestController{

    public function test(){
        echo 'test jt server';
    }
    public function addJtData($num,$depId){

        if (isset($num)&&is_numeric($num)) {

            $createDate = date('Y-m-d H:i:s');
            for($i=0;$i<$num;$i++){
                $modelTerminal = D('JtTerminal');
                $data  = array();
                $data['createDate'] = $createDate;
                $data['devNo']      = '123';
                $data['termType']   = '123';
                $data['makeFactory']= '123';
                $data['makeNo']     = '123';
                $data['termNo']     = 'MTP0'.(100+$i);
                $data['bind']       = (bool)true;
                //插入终端
                if (!$modelTerminal->create($data,Model::MODEL_INSERT)) {
                     echo 'error create terminal data';
                } else {
                    $res = $modelTerminal->add();
                    if ($res>0) {
                        //插入车辆
                        $modelVehicle = D('JtVehicle');
                        $dataVehicle  = array();
                        $dataVehicle['createDate'] = $createDate;
                        $dataVehicle['plateNo']    = '测A'.(9000+$i);
                        $dataVehicle['simNo']      = '19'.(900000000+$i);
                        $dataVehicle['plateColor'] = 1;
                        $dataVehicle['termId']     = $res;
                        $dataVehicle['depId']      = $depId;
                        if(!$modelVehicle->create($dataVehicle,Model::MODEL_INSERT)){
                            echo 'error create vehicle data';
                        }
                        $resVehicle = $modelVehicle->add();
                        if(!$resVehicle){
                            echo 'error add vehicle';
                        }

                    } else {
                        echo 'error add terminal';
                    }
                }
            }
        }

    }

}