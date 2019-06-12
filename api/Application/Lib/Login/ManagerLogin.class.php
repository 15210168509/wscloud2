<?php
/**
 * Created by wrf.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:39
 */

namespace Lib\Login;
use Lib\Msg;
use Lib\Status;
use Lib\CommonConst;
use Lib\StatusCode;
use Lib\Tools;
use Think\Model;

class ManagerLogin extends LoginInstance
{

    protected function doSelectByAccount($account, $pwd)
    {
        //判断用户是否已登录
        $model = D('Manager');

        //检索条件
        $where['account'] = array('EQ', $account);
        $where['phone']   = array('EQ', $account);
        $where['email']   = array('EQ', $account);
        $where['_logic']  = 'or';
        $map['_complex']  = $where;
        $map['password']  = array('EQ',$pwd);
        $map['status']    = array('EQ',Status::MANAGER_OK);
        $map['del_flg']   = array('EQ',CommonConst::DEL_FLG_OK);

        //查询语句
        $result = $model->fetchSql(false)->where($map)->find();
        if (!empty($result)){

            $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,true);
            $this->getContext()->setParams(AbstractLogin::LOGIN_INFO,$result);

        } else {
            $this->getContext()->setParams(AbstractLogin::LOGIN_LABEL,false);
            $this->getContext()->setError(Msg::LOGIN_ERROR);
            $this->getContext()->setStatusCode(StatusCode::LOGIN_ERROR);
        }

        return $result;
    }

    /**
     * 将refreshToken保存到数据库
     * author 李文起
     * @param $res
     * @param $token
     * @param int $expireTime
     * @return bool
     */
    public function setRefreshTokenToDB($res,$token,$expireTime=0){
        $model = D('ManagerToken');

        $map['manager_id']        = $res['id'];
        $map['del_flg']         = CommonConst::DEL_FLG_OK;
        $map['type']             = $this->getContext()->get(AbstractLogin::LOGIN_TYPE);

        $data['refresh_token'] = $token['refresh_token'];
        $data['detail']         = $token['token'].'_'.$res['id'];
        $data['type']           = $this->getContext()->get(AbstractLogin::LOGIN_TYPE);
        $data['expire_time']   = $expireTime;

        //先查询
        $result = $model->where($map)->find();

        //如果已经有refresh_token  更新token
        if (!empty($result)){
            if (!$model->create($data,Model::MODEL_UPDATE)){
                return false;
            } else {
                $result = $model->where($map)->save();
                if ($result !==false ){
                    return true;
                }
            }

            //如果无则添加一条
        } else {

            $data['manager_id']        = $res['id'];
            $data['id']              = Tools::generateId();

            if (!$model->create($data,Model::MODEL_INSERT)){
                return false;
            } else {
                $res = $model->add();
                if ($res !==false ){
                    return true;
                }
            }
        }
        return false;
    }
}