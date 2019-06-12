<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/7/27
 * Time: 17:22
 */

namespace Home\Controller;


use Lib\CommonConst;
use Lib\JPushTools;
use Lib\Mqtt\MsgPublish;
use Lib\PredisClient;
use Lib\RedisData;
use Lib\RedisLock;
use Lib\RedisTest;
use Lib\Tools;
use Think\Template\TagLib;

class TestController
{
    function createGeo(){
        echo 'test';
    }
    function pushTest() {
        $res = JPushTools::getInstance('fast','fast')->getClientInfo();
        $fast = JPushTools::getInstance('test','test')->getClientInfo();
        $fast2 = JPushTools::getInstance('test','test')->getClientInfo();
        echo $res.'<br />';
        echo $fast.'<br />';
        echo $fast2.'<br />';
    }
    function driver($companyId = 'null'){

        phpinfo();

        /*$model = D('Driver');

        $map['del_flg']     = 0;
        if($companyId != 'null') {
            $map['company_id'] = $companyId;
        }

        $res = $model->where($map)->select();

        $redis = RedisLock::getInstance();

        foreach ($res as $key=>$value) {
            $info['driver_id']  =   $value['id'];
            $info['company_id'] =  $value['company_id'];
            $info['name']       = $value['name'];
            $redis->set('safe_'.$value['ws_open_id'],json_encode($info),0);
        }*/
    }

    /**
     * 设置设备绑定信息
     * author 李文起
     * @param $companyId
     */
    function device($companyId = 'null'){

        $model = D('Device');

        $map['d.del_flg']     = CommonConst::DEL_FLG_OK;
        $map['v.del_flg']     = CommonConst::DEL_FLG_OK;
        if($companyId != 'null') {
            $map['d.company_id'] = $companyId;
        }

        $res = $model
            ->alias('d')
            ->field('d.serial_no,d.company_id,v.vehicle_no')
            ->join('left join vehicle as v on d.serial_no = v.device_no')
            ->where($map)
            ->select();

        $redis = RedisLock::getInstance();

        foreach ($res as $key=>$value) {
            $redis->set('safe_device_'.$value['serial_no'],json_encode(array('vehicle_no'=>$value['vehicle_no'],'company_id'=>$value['company_id'])),0);
        }
    }


    /**
     * 设置默认管理员信息
     * author 李文起
     */
    public function setDefaultAdminInfo($companyId){
        $model = D('Admin');

        $map['type'] = CommonConst::ADMIN_TYPE_ADMIN;
        $map['company_id'] = $companyId;

        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        $res = $model->field('id,phone')->where($map)->select();


        $redis = RedisLock::getInstance();
        $redis->set('safe_admin_'.$companyId,json_encode($res),0);

    }

    public function testRedis(){
        /*$redis = RedisLock::getInstance();
        $redis->set('test_token',123,10);*/
        //$redis = RedisTest::getInstance();
        //dump($redis->set('name','dty'));
        //echo phpinfo();
        $redis = PredisClient::getInstance();
        dump($redis->get('name'));
        //dump($redis->setnx('name1','dty'));
    }

    public function topic()
    {
        /*$model = D('Admin');
        $map['id'] = 1587253286;
        $pare = json_decode(file_get_contents("php://input"),true);
        $data['name'] = $pare["Message"];
        $data['del_flg'] = 1;
        $model->where($map)->save($data);*/
        $res = JPushTool::getInstance()->sendMessageByAlias('869455045163868','消息','设备配置信息',array('type'=>40,'topic'=>'12312','serialNo'=>'1213'));
        dump($res);
    }

    /**
     * lua脚本测试
     * author 李文起
     */
    public function redisLuaTest(){

        $redis = RedisLock::getInstance();
        $script='return redis.call("set",KEYS[1],ARGV[1])';
        $hash=$redis->script('load',$script);
        $hashresult=$redis->evalSha($hash,['aaa','wen'],1);
        //最后一个参数指的是能够通过lua脚本中keys[n]访问到的参数数量，剩下的都通过argv[n]获取
        var_dump($hashresult);
        exit;
    }

    public function apolloTest(){
        MsgPublish::getInstance()->sendMsg(CommonConst::TOPIC_ADMIN . '/13373944335' , json_encode(array('msgType' => CommonConst::MSG_TYPE_TIRED, 'data' => '123456')));
    }

    public function setCompanyDeviceSetting($companyId,$userId)
    {
        $setting = C('DEVICE_SETTING');
        $arr = [];
        foreach ($setting as $k=>$v) {
            $data = [];
            $data['id'] = Tools::generateId();
            $data['company_id'] = $companyId;
            $data['type'] = $k;
            $data['value'] = $v;
            if ($k == 60 || $k== 80 || $k== 90) {
                $data['is_common'] = 0;
            } else {
                $data['is_common'] = 1;
            }
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['create_user'] = $userId;
            $data['update_user'] = $userId;
            $arr[] = $data;
        }
        $model = D('DeviceSetting');
        $res = $model->addAll($arr);
        if (!$res) {
            echo 'no';
        } else {
            echo 'ok';
        }
    }

}
