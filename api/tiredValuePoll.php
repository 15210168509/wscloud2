<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/23
 * Time: 10:59
 */
//header('location:http://localhost:133/Poll/uploadTiredValue');
while (true){
    $res = file_get_contents('http://localhost:133/Poll/uploadTiredValue');
    echo ($res);
    sleep(5);
}