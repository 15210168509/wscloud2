<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/11
 * Time: 16:48
 */

namespace Home\Model;


use Think\Model;

class GroupsModel extends Model
{
    protected $connection = 'DB_CONFIG1';
    protected $tableName  = "groups";
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );
}