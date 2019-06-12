<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/8
 * Time: 10:58
 */

namespace Lib\Login;


use Lib\CommonConst;

abstract class AbstractLogin implements \SplSubject
{
    //是否登录标签
    const LOGIN_LABEL = 'login';
    //登录信息key
    const LOGIN_INFO = 'loginInfo';
    //登录类型
    const LOGIN_TYPE = 'loginType';
    //登录方式
    const LOGIN_METHOD = 'loginMethod';
    //观察者容器
    private $storage;

    //上下文参数
    protected $context;

    protected $login;

    public function __construct(AbstractLogin $login=null)
    {

        if ($login == null){
            $this->storage = new \SplObjectStorage();
            $this->context = new LoginContext();
        } else {
            $this->storage = $login->storage;
            $this->context = $login->getContext();
        }

        $this->login = $login;
    }

    /**
     * 用户名密码登陆
     * author 李文起
     * @param $account
     * @param $pwd
     * @param $loginType
     */
    public function loginByAccount($account,$pwd,$loginType){
        $this->context->setParams('account',$account);
        $this->context->setParams('password',$pwd);
        $this->context->setParams(self::LOGIN_TYPE,$loginType);
        $this->context->setParams(self::LOGIN_METHOD,CommonConst::LOGIN_BY_ACCOUNT);
    }

    //添加观察者
    public function attach(\SplObserver $observer){
        $this->storage->attach($observer);
    }
    //移除观察者
    public function detach(\SplObserver $observer){
        $this->storage->detach($observer);
    }
    //通知观察者
    public function notify(){
        foreach($this->storage as $obs){
            $obs->update($this);
        }
    }
    //登录逻辑处理
    public function handleLogin(){
        $this->doLogin();
        //通知观察者
        $this->notify();
    }
    //返回上下文
    public function getContext(){
        return $this->context;
    }
    //登录逻辑处理
    public function doLogin(){
        //根据登陆方式调用相应方法登陆
        switch ($this->context->get(self::LOGIN_METHOD)) {
            case CommonConst::LOGIN_BY_ACCOUNT : {

                $this->doSelectByAccount($this->context->get('account'),$this->context->get('password'));
                break;
            }
        }


    }
    //抽象类，查询数据，需要子类实现此方法
    protected abstract function doSelectByAccount($account,$pwd);

}