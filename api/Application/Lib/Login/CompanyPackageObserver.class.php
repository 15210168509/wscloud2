<?php
/**
 * 登录观察者：公司套餐检查
 * Created by dbn.
 * User: 02
 * Date: 2017/11/07
 * Time: 9:32
 */

namespace Lib\Login;

use Lib\CommonConst;
use Lib\Msg;
use Lib\Package;
use Lib\Status;
use Lib\StatusCode;

class CompanyPackageObserver extends LoginObserver
{
    public function doUpdate(AbstractLogin $login)
    {
        //用户登录成功，获取用户公司信息
        if($this->longContext->get(AbstractLogin::LOGIN_LABEL)){

            //获取用户信息
            $userInfo = $this->longContext->get(AbstractLogin::LOGIN_INFO);

            //获取公司信息
            $companyInfo = $this->getCompanyInfo($userInfo['company_id']);

            // 判断公司是否在有效套餐内
            if ($userInfo && $companyInfo['verify_status'] == CommonConst::VERIFY_STATUS_OK) {

                if (!$this->checkPackageEffective($userInfo['company_id'])) {
                    $this->longContext->setParams(AbstractLogin::LOGIN_LABEL,false);
                    $this->longContext->setError(Msg::COMPANY_PACKAGE_EXPIRE);
                    $this->longContext->setStatusCode(StatusCode::COMPANY_PACKAGE_EXPIRE);
                }
            }
        }
    }

    /**
     * 检测套餐是否过期
     * author 李文起
     * @param $companyId
     * @return int
     */
    public function checkPackageEffective($companyId){
        $model             = D('CompanyPackage');

        $map['company_id'] = array('EQ', $companyId);
        $map['start_time'] = array('ELT',time());
        $map['end_time']   = array('GT',time());

        $map['status']     = array('EQ',Status::ADMIN_OK);
        $map['del_flg']    = array('EQ', CommonConst::DEL_FLG_OK);
        $res = $model->where($map)->find();

        //套餐存在
        if (count($res) > 0) {
            return $res['end_time'];
        }
        return  0;
    }

    /**
     * 获取公司信息
     * author 李文起
     * @param $companyId
     * @return mixed
     */
    public function getCompanyInfo($companyId){
        $model = D('Company');

        $map['id']      = $companyId;
        $map['del_flg'] = CommonConst::DEL_FLG_OK;

        return $model->where($map)->find();
    }
}