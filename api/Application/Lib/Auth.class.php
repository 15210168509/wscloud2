<?php
/**
 * 用户认证相关
 * Created by PhpStorm.
 * User: 01
 * Date: 2017/7/6
 * Time: 10:03
 */

namespace Lib;

use Lib\RedisData;
use Think\Model;

class Auth extends HObject
{
    const TOKEN_TIME         = 7200;
    const REFRESH_TOKEN_TIME = 2592000;
    const OLD_TOKEN          = 'old_token';
    const TOKEN_PREFIX       = 'token_';
    const REFRESH_PREFIX     = 'refresh_';
    const API_LIMIT          = 'apiLimit_';
    const API_LIMIT_TIME     = 10;              //同一天内接口调用次数限制
    const API_STAT_EXPIRE    = 86400;           //过期时间

    /**
     * 第三方开放接口，根据账号密码获取token，refresh_token
     * @param $account string 第三方账号
     * @param $pwd string 第三方密码,明文
     * @return mixed
    */
    public function getToken($account,$pwd){

        //判断用户已存在
        $model = D('Auth');
        //检索条件
        $map['account']     = array('eq',$account);
        $map['pwd']         = array('eq',md5($pwd));
        $map['status']      = array('eq', Status::ADMIN_OK);
        $map['del_flg']     = CommonConst::DEL_FLG_OK;

        //查询结果
        $res = $model->where($map)->find();
        //用户存在
        if ($res&&count($res)>0) {
            //是否到达接口调用上限
            if ($this->addStat('getToken',$res['id'])) {
                return $this->generateTokenById($res['id'],"10");
            } else {
                $this->setError(Msg::API_LIMIT_EXCEEDED);
                return false;
            }

        } else {
            //用户不存在
            $this->setError(Msg::USER_NOT_EXIST);
            return false;
        }
    }
    /**
     * 接口统计，上限判断
     * @param $methodName string 调用接口的方法名
     * @param $userId int 调用者流水号
     * @return bool
    */
    private function addStat($methodName,$userId){
        $storeRedis = RedisData::getRedis();
        //获取调用次数
        $key   = self::API_LIMIT.$methodName.'_'.$userId.'_'.date('Y-m-d');
        $times = $storeRedis->get($key,$userId);
        //如果不存在，则添加
        if (!$times) {
            $storeRedis->set($key,1,self::API_STAT_EXPIRE);
        } else {
            //判断是否到达接口调用上限

            if ($times > self::API_LIMIT_TIME) {
                return false;
            } else {
                //增加调用次数
                $times = $times+1;
                $storeRedis->set($key,$times,self::API_STAT_EXPIRE);
            }
        }
        return true;
    }
    /**
     * 根据用户ID，生成token，refresh_token
     * @param $userId int
     * @param $type
     * @return string
    */
    public function generateTokenById($userId,$type){

        $storeRedis      = RedisData::getRedis();
        //获取旧token
        $oldToken        = $storeRedis->get(self::OLD_TOKEN.'_'.$type.'_'.$userId);
        //旧token存在，则标记用户原有为已过期（可能多次重新登录）
        if ($oldToken) {
            $storeRedis->set(self::TOKEN_PREFIX.$oldToken,CommonConst::OTHER_DEVICE_LOGIN,self::TOKEN_TIME);
        }
        //生成新的token和refreshToken
        $token        = md5(time().$userId);
        $refreshToken = md5(time().$userId);
        //保存生成的token和refreshToken,为了通过用户id反向查找
        $storeRedis->set(self::OLD_TOKEN.'_'.$type.'_'.$userId,$token,self::TOKEN_TIME);
        //设置token
        $storeRedis->set(self::TOKEN_PREFIX.$token,$userId,self::TOKEN_TIME);

        //设置refresh_token
        return array('token'=>$token,'refresh_token'=>$refreshToken);
    }

    /**
     * 验证token
     * @param $token string 验证的token
     * @return mixed
    */
    public function checkToken($token){
        $storeRedis = RedisData::getRedis();
        $userId = $storeRedis->get(self::TOKEN_PREFIX.$token);
        //token不存在或已过期
        if($userId==''|| $userId ==false){
            return false;
        }
        return $userId;
    }
    /**
     * 刷新token
     * @param $oldToken string 旧的过期token
     * @param $res  array  查询结果
     * @return mixed
    */
    public function refreshToken($oldToken,$res){

        $token = explode('_',$res['detail']);
        if ($token[0] != $oldToken) {
            return false;
        } else {
            return $this->generateTokenByRefreshToken($token[1],$oldToken,$res['type']);
        }

    }
    private function generateTokenByRefreshToken($userId,$oldToken,$type){

        $token        = md5(time().$userId);
        $storeRedis   = RedisData::getRedis();

        //删除旧token
        $storeRedis->rm(self::TOKEN_PREFIX.$oldToken);
        //保存生成的token和refreshToken,为了通过用户id反向查找
        $storeRedis->set(self::OLD_TOKEN.'_'.$type.'_'.$userId,$token,self::TOKEN_TIME);
        //保存新token
        $storeRedis->set(self::TOKEN_PREFIX.$token,$userId,self::TOKEN_TIME);

        return array('token'=>$token);

    }

    /**
     * 退出登录
     * author 李文起
     * @param $userId
     * @param $type
     * @return string
     */
    public function logoutClearToken($userId,$type){
        $storeRedis   = RedisData::getRedis();
        $oldToken     = $storeRedis->get(self::OLD_TOKEN.'_'.$type.'_'.$userId);
        $storeRedis->rm(self::TOKEN_PREFIX.$oldToken);
    }
}