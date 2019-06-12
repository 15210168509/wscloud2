<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/22
 * Time: 10:09
 */

namespace Home\Model;


use Think\Model;

class DrivingMonitorModel extends Model
{
    protected $connection = 'DB_CONFIG2';
    protected $tableName  = "user_behavior";
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );
}