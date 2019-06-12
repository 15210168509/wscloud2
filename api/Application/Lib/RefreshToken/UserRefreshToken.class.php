<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/3
 * Time: 15:20
 */

namespace Lib\RefreshToken;


use Lib\CommonConst;
use Think\Model;

class UserRefreshToken extends RefreshToken
{
    public function refreshToken(){
        $model = D('UserToken');

        $map['del_flg']         = CommonConst::DEL_FLG_OK;
        $map['refresh_token']  = $this->refreshToken;
        $res = $model->where($map)->find();

        if (!empty($res)){
            $result =  $this->refreshTokenValue($res);

            if ($result != false && !empty($result)) {
                $data['detail'] = $result['token'].'_'.$res['user_id'];

                if ($model->create($data,Model::MODEL_UPDATE)) {

                    $res = $model->where($map)->save();
                    if ($res !== false) {
                        return $result;
                    }
                }
            }
        }
        return false;

    }
}