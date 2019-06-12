<?php
/**
 * 登录观察者抽象类
 * User: wrf
 * Date: 2017/8/29
 * Time: 11:19
 */

namespace Lib\LogOut;


abstract class LogOutObserver implements \SplObserver
{
    private $logOut;
    protected $longContext;

    function __construct(LogOut $logOut)
    {
        $this->logOut      = $logOut;
        $this->longContext = $logOut->getContext();
        $logOut->attach($this);
    }
    function update(\SplSubject $splSubject){
        if($splSubject == $this->logOut){
            $this->doUpdate($splSubject);
        }
    }
    abstract function doUpdate(LogOut $logOut);
}