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
use Lib\StatusCode;

class DriverController extends AdvancedRestController
{
    /**
     * author 李文起
     * @param $pageNo
     * @param $pageSize
     * @param string $name
     * @param string $phone
     * @param string $status
     * @param string $companyId
     */
    public function driverLists($pageNo, $pageSize, $name = 'null', $phone = 'null', $status = 'null',$companyId='null')
    {
        $model = D('Driver');

        $map['d.del_flg']     = array('EQ', CommonConst::DEL_FLG_OK);

        if (isset($name) && $name != 'null') {
            $map['d.name'] = array('LIKE', '%'.addslashes($name).'%');
        }
        if (isset($phone) && $phone != 'null') {
            $map['d.phone'] = array('LIKE', '%'.addslashes($phone).'%');
        }
        if (isset($status) && $status != 'null') {
            $map['d.status'] = array('EQ', addslashes($status));
        }
        if ($companyId != 'null'){
            $map['d.company_id']  = $companyId;
        }

        $totalRecord = $model
            ->alias('d')
            ->join('left join company c on d.company_id = c.id')
            ->where($map)
            ->count();

        $num = ceil($totalRecord/$pageSize);
        if ($pageNo > $num) {
            $pageNo = $num;
        }

        $result = $model
            ->alias('d')
            ->field('d.*,c.name company_name')
            ->join('left join company c on d.company_id = c.id')
            ->where($map)
            ->page($pageNo, $pageSize)
            ->order('create_time DESC')
            ->select();

        if (count($result) > 0) {

            foreach ($result as &$value) {
                $value['certification_expire_time'] = $value['certification_expire_time'] ? date('Y-m-d', $value['certification_expire_time']) : '无';
                $value['create_time'] = $value['create_time'] ? date('Y-m-d H:i:s', $value['create_time']) : '无';
                $value['status_name'] = Status::AdminStatus2Str($value['status']);
                $value['sex_name']    =  $value['sex'] == 1 ? '男':'女';
            }

            $this->setReturnVal(Code::OK, Msg::OK, StatusCode::OK,array('dataList'=>$result, 'totalRecord'=>$totalRecord));
        } else {
            $this->setReturnVal(Code::ERROR, Msg::NO_DATA,StatusCode::NO_DATA);
        }
        $this->restReturn();
    }
}