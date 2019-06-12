<?php
/**
 * 登录观察者抽象类
 * User: wrf
 * Date: 2017/8/29
 * Time: 11:19
 */

namespace Lib\Login;


abstract class LoginObserver implements \SplObserver
{
    private $login;
    protected $longContext;

    function __construct(AbstractLogin $login)
    {
        $this->login       = $login;
        $this->longContext = $login->getContext();
        $login->attach($this);
    }
    function update(\SplSubject $splSubject){
        if($splSubject == $this->login){
            $this->doUpdate($splSubject);
        }
    }
    abstract function doUpdate(AbstractLogin $login);
}