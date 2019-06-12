<?php
/**
 * 注册表基础类，便于数据跨层请求
 * Created by PhpStorm.
 * User: wrf
 * Date: 2017/7/5
 * Time: 14:33
 */

namespace Lib;


abstract class Registry
{
    abstract protected function get($key);
    abstract protected function set($key,$value);

}