<?php
/**
 * Created by 李文起
 * User: 01
 * Date: 2018/5/3
 * Time: 13:39
 */

namespace Home\Model;


use Think\Model;

class DriverTiredValueModel extends Model
{
    protected $connection = 'DB_CONFIG1';
    protected $tableName  = "driver_tired_value";
}