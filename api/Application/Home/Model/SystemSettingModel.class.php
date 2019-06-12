<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/6/12
 * Time: 16:03
 */

namespace Home\Model;


use Think\Model;

class SystemSettingModel extends Model
{
    protected $connection = 'DB_CONFIG1';
    protected $tableName  = "system_setting";
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );
}