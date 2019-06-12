<?php
/**
 * 观察话题
 * User: wrf
 * Date: 2017/8/29
 * Time: 11:14
 */

namespace Lib\LogOut;
use Lib\CommonConst;
use Lib\Status;

abstract class LogOut implements \SplSubject
{

    //是否退出标签
    const LOGOUT_LABEL = 'logOut';

    //用户信息key
    const LOGOUT_INFO = 'logOutInfo';

    //退出者的id
    const LOGOUT_ID = 'logoutId';     //如果是用户id 则是userId值，如果是管理员则是管理员 adminId

    //退出者类型
    const LOGOUT_TYPE = 'logoutType';


    //观察者容器
    private $storage;

    //上下文参数
    protected $context;
    public function __construct($id,$type)
    {
        $this->storage = new \SplObjectStorage();
        $this->context = new LogOutContext();
        $this->context->setParams(LogOut::LOGOUT_ID,$id);
        $this->context->setParams(LogOut::LOGOUT_TYPE,$type);
        $this->context->setParams(LogOut::LOGOUT_LABEL,true);
        $this->context->setParams(LogOut::LOGOUT_INFO, $this->doSelect($id));
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
    public function handleLogOut(){
        //通知观察者
        $this->notify();
    }
    //返回上下文
    public function getContext(){
        return $this->context;
    }
    //抽象类，查询数据，需要子类实现此方法
    protected abstract function doSelect($adminId);
}