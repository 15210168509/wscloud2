<?php

/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/2/27
 * Time: 12:11
 */
namespace Home\Model;
use Think\Model;

class ManagerMsgModel extends Model
{
    protected $connection = 'DB_CONFIG1';
    protected $tableName  = "manager_msg";
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );

}