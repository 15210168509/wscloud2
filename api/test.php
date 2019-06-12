<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2019/1/25
 * Time: 10:15
 */
    $redis = new \Redis();
    $redis->connect('www.56xun.cn',6379);
    $redis->incr('testNum');
    echo '{"code":1}';