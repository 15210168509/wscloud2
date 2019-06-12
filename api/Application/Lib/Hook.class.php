<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/3/9
 * Time: 11:50
 */

namespace Lib;


abstract class Hook
{
    /**
     * 执行行为 run方法是Behavior唯一的接口
     * @access public
     * @param mixed $params  行为参数
     * @return void
     */
    abstract public function run(&$params);
}