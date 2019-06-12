<?php
/**
 * Created by PhpStorm.
 * User: WuRuifeng
 * Date: 14-12-16
 * Time: 下午2:48
 */
namespace Lib;

/**
 * Class BaseController
 * @package Lib
 */
abstract class ListManagementController extends BaseManagementController {

    //public $filterCondition = array();
    private $filterCondition = array();
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 设置参数
     * @param $para
     */
    protected  function setFilterCondition($para){
        $this->filterCondition = $para;
    }

    /**
     * 设置过滤条件
     * @param $value
     * @param $checkType
     * @return false|int|string
     */
    private function checkParam($value,$checkType){
        switch($checkType){
            case 'string': return mb_strlen($value) > 0 ? urlencode($value) : 'null';
            case 'int':    return intval($value) ? intval($value) : 'null';
            case 'stime' : return strtotime($value) > 0 ? strtotime($value) : 'null';
            case 'etime' : return strtotime($value) > 0 ? (strtotime($value)+86439) : 'null';
            default:return $value;
        }
    }

    /**
     * 格式化时间
     * @param $value
     * @param $checkType
     * @return false|int|string
     */
    private function checkTimePara($value,$checkType){
        switch($checkType){
            case 'stime' : return strtotime($value) > 0 ? strtotime($value) : 'null';
            case 'etime' : return strtotime($value) > 0 ? (strtotime($value)+86439) : 'null';
            default:return $value;
        }
    }

    //获取session保存的搜索条件
    public function getSessionParam($dataSource){

        $session_para = $this->getState($dataSource.'_list_where');
        return $session_para;
    }

    //列表搜索
    public function getList($dataSource,$callback){

        //初始化数据库
        $model = D($dataSource);
        $para = array();
        $para_source = array();
        foreach ($this->filterCondition as $condition=>$checkType) {
            $para_source[$condition] = $this->checkTimePara(trim(I($condition)),$checkType);
            $para[$condition] = $this->checkParam(trim(I($condition)),$checkType);
        }
        $is_bt = intval(I('get.is_bt'), 0);
        if ($is_bt === 1) {
            $this->setState($dataSource.'_list_where', $para_source);
        }

        $session_para = $this->getState($dataSource.'_list_where');

        //如果保存条件不为空，则覆盖已有的数据
        if ($session_para) {
            $para = $session_para;
        }
        $para["pageSize"]   = I('limit') ? I('limit'):10;
        $para["pageNo"]     = I('page');

        //构建url参数
        if (is_array($para) && count($para)>0) {
            $res = $model->getList($para);
            $res = json_decode(json_encode($res));
            if ($res->code == Code::OK) {
                $list =  $this->$callback($res->data->dataList);
                $_res = array(
                    "draw" => $_GET["draw"],
                    "recordsTotal" => $res->data->totalRecord,
                    "recordsFiltered" => $res->data->totalRecord,
                    "data" => $list,
                    'code'=>0,
                    'count'=>$res->data->totalRecord,
                );
                if (C("Debug")) {
                    $_res["Debug"] = array("java" => $res, "paras" => $para);
                }
                return $_res;
            } else {
                $_res = array(
                    "draw" => $_GET["draw"],
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    'code'=>0,
                    "data" => array()
                );
                return $_res;
            }
        }

        return false;
    }

}