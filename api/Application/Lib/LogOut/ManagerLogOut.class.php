<?php
/**
 * Created by wrf.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:39
 */

namespace Lib\LogOut;
use Lib\Auth;
use Lib\Msg;
use Lib\Status;
use Lib\CommonConst;
use Lib\StatusCode;
use Think\Model;

class ManagerLogOut extends LogOut
{
    function doSelect($id)
    {
        $model = D('Manager');
        //检索条件
        $map['id']          = array('eq',$id);
        $map['status']      = array('eq', Status::MANAGER_OK);
        $map['del_flg']     = CommonConst::DEL_FLG_OK;

        $res = $model->where($map)->find();
        $context = $this->getContext();

        if (!empty($res)){

            $result = $this->clearToken($res,$context->get(LogOut::LOGOUT_TYPE));
            if ($result){
                return $res;
            } else {
                $context->setParams(LogOut::LOGOUT_LABEL,false);
                $context->setError(Msg::DEL_TOKEN_ERROR);
                $context->setStatusCode(StatusCode::LOGIN_ERROR);
            }
        } else {
            $context->setParams(LogOut::LOGOUT_LABEL,false);
            $context->setError(Msg::LOGOUT_ERROR);
            $context->setStatusCode(StatusCode::LOGOUT_ERROR);
        }
        return array();
    }

    /**
     * 退出清除token
     * author 李文起
     * @param $result
     * @param $type
     * @return string
     */
    private function clearToken($result,$type){
        $auth = new Auth();
        $auth->logoutClearToken($result['id'],$type);

        $model = D('ManagerToken');
        $map['manager_id']         = $result['id'];
        $map['del_flg']         = CommonConst::DEL_FLG_OK;
        $map['type']             = $type;

        $data['refresh_token'] = '';
        $data['detail']         = '';
        $data['expire_time']   = 0;

        if ($model->create($data,Model::MODEL_UPDATE)){
            $res = $model->where($map)->save();
            if ($res !==false ){
                return true;
            }
        }
        return false;
    }
}