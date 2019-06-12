<?php
/**
 * Created by PhpStorm.
 * User: 02
 * Date: 2017/2/10
 * Time: 16:18
 */
namespace Lib\Strategy;

class TestStrategy implements IStrategy
{
    public function run($param)
    {
        $param = 'test hook class!';
        return $param;
    }
}