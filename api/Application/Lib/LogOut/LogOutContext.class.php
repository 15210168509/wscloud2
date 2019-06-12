<?php
/**
 * Created by wrf.
 * User: Thinkpad
 * Date: 2017/8/29
 * Time: 11:29
 */

namespace Lib\LogOut;


class LogOutContext
{
    private $params = array();
    private $error      = '';
    private $statusCode  = '';

    function __construct()
    {

    }
    function setParams($key,$val){
        $this->params[$key] = $val;
    }
    function get($key){

        return isset($this->params[$key])?$this->params[$key]:false;
    }
    function setError($error){
        $this->error = $error;
    }
    function getError(){
        return $this->error;
    }

    function setStatusCode($statusCode){
        $this->statusCode = $statusCode;
    }
    function getStatusCode(){
        return $this->statusCode;
    }
}