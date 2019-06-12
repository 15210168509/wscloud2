<?php
/**
 * 基础对象类
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/6/15
 * Time: 15:45
 */

namespace Lib;

use Lib\Logger\Logger;

class HObject
{
    private $error;

    public function setError($error){
        $this->error = $error;
        Logger::error($error);
    }
    public function getError(){
        return $this->error;
    }
}