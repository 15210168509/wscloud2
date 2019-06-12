<?php

namespace Lib\TaskQueue\Handle;

/**
 * 处理程序接口类
 * User: dbn
 * Date: 2017/9/30
 * Time: 9:32
 */

interface IHandle
{
    /**
     * 正常处理程序
     * 注意》》程序中要对需要的参数进行验证，防止参数错误
     * @param  array $param 参数数组
     * @return boolean
     */
    public function run($param);

    /**
     * run方法返回false，执行失败，调用一次此方法
     * 注意》》程序中要对需要的参数进行验证，防止参数错误
     * @param  array $param 参数数组
     * @return mixed
     */
    public function errorCallbackRun($param);

    /**
     * run方法返回true，执行成功，但是其他关联的Handle执行失败，会调用一次此方法
     * 注意》》程序中要对需要的参数进行验证，防止参数错误
     * @param  array $param 参数数组
     * @return mixed
     */
    public function successCallbackRun($param);
}