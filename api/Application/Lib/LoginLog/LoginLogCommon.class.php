<?php

namespace Lib\LoginLog;

use Lib\CommonConst;
use Lib\RedisData;
use Lib\Status;

/**
 * 公共方法
 * User: dbn
 * Date: 2017/10/11
 * Time: 13:11
 */
class LoginLogCommon
{
    protected $_loginLog;
    protected $_redis;

    public function __construct(LoginLog $loginLog)
    {
        $this->_loginLog = $loginLog;
        $this->_redis    = RedisData::getRedis();
    }

    /**
     * 验证用户类型
     * @param  string $type 用户类型
     * @return boolean
     */
    protected function checkType($type)
    {
        if (in_array($type, array(
            CommonConst::PC_ADMIN,
            CommonConst::H5_ADMIN,
            CommonConst::PC_USER,
            CommonConst::H5_USER,
        ))) {
            return true;
        }
        $this->_loginLog->setError('未定义的日志类型：' . $type);
        return false;
    }

    /**
     * 验证唯一标识
     * @param  string  $uid
     * @return boolean
     */
    protected function checkUid($uid)
    {
        if (is_numeric($uid) && $uid > 0) {
            return true;
        }
        $this->_loginLog->setError('唯一标识非法：'  . $uid);
        return false;
    }

    /**
     * 验证时间戳
     * @param  string  $time
     * @return boolean
     */
    protected function checkTime($time)
    {
        if (is_numeric($time) && $time > 0) {
            return true;
        }
        $this->_loginLog->setError('时间戳非法：' . $time);
        return false;
    }

    /**
     * 验证时间是否在当月中
     * @param  string $time
     * @return boolean
     */
    protected function checkTimeWhetherThisMonth($time)
    {
        if ($this->checkTime($time) && $time > strtotime(date('Y-m')) && $time < strtotime(date('Y-m') . '-' . date('t'))) {
            return true;
        }
        $this->_loginLog->setError('时间未在当前月份中：' . $time);
        return false;
    }

    /**
     * 验证时间是否超过当前时间
     * @param  string $time
     * @return boolean
     */
    protected function checkTimeWhetherFutureTime($time)
    {
        if ($this->checkTime($time) && $time <= time()) {
            return true;
        }
        return false;
    }

    /**
     * 验证开始/结束时间
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return boolean
     */
    protected function checkTimeRange($startTime, $endTime)
    {
        if ($this->checkTime($startTime) &&
            $this->checkTime($endTime) &&
            $startTime < $endTime &&
            $startTime < time()
        ) {
            return true;
        }
        $this->_loginLog->setError('时间范围非法：' . $startTime . '-' . $endTime);
        return false;
    }

    /**
     * 验证时间是否在指定范围内
     * @param  string $time      需要检查的时间
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return boolean
     */
    protected function checkTimeWithinTimeRange($time, $startTime, $endTime)
    {
        if ($this->checkTime($time) &&
            $this->checkTimeRange($startTime, $endTime) &&
            $startTime <= $time &&
            $time <= $endTime
        ) {
            return true;
        }
        $this->_loginLog->setError('请求时间未在时间范围内：' . $time . '-' . $startTime . '-' . $endTime);
        return false;
    }

    /**
     * 验证Redis日志记录标准Key
     * @param  string  $key
     * @return boolean
     */
    protected function checkLoginLogKey($key)
    {
        $pattern = '/^loginLog_\d{4}-\d{1,2}_\S+_\d+$/';
        $result = preg_match($pattern, $key, $match);
        if ($result > 0) {
            return true;
        }
        $this->_loginLog->setError('RedisKey非法：' . $key);
        return false;
    }

    /**
     * 获取月份中有多少天
     * @param  int $time 时间戳
     * @return int
     */
    protected function getDaysInMonth($time)
    {
        return date('t', $time);
    }

    /**
     * 对没有前导零的月份或日设置前导零
     * @param  int $num 月份或日
     * @return string
     */
    protected function setDateLeadingZero($num)
    {
        if (is_numeric($num) && strlen($num) <= 2) {
            $num = (strlen($num) > 1 ? $num : '0' . $num);
        }
        return $num;
    }

    /**
     * 验证过期时间
     * @param  int     $existsDay 一条记录在Redis中过期时间，单位：天，必须大于31
     * @return boolean
     */
    protected function checkExistsDay($existsDay)
    {
        if (is_numeric($existsDay) && ctype_digit(strval($existsDay)) && $existsDay > 31) {
            return true;
        }
        $this->_loginLog->setError('过期时间非法：' . $existsDay);
        return false;
    }

    /**
     * 获取开始日期边界
     * @param  int $time      需要判断的时间戳
     * @param  int $startTime 起始时间
     * @return int
     */
    protected function getStartTimeBorder($time, $startTime)
    {
        $initDay = 1;
        if ($this->checkTime($time) && $this->checkTime($startTime) &&
            date('Y-m', $time) === date('Y-m', $startTime) && false !== date('Y-m', $time)) {
            $initDay = date('j', $startTime);
        }
        return $initDay;
    }

    /**
     * 获取结束日期边界
     * @param  int $time      需要判断的时间戳
     * @param  int $endTime   结束时间
     * @return int
     */
    protected function getEndTimeBorder($time, $endTime)
    {
        $border = $this->getDaysInMonth($time);
        if ($this->checkTime($time) && $this->checkTime($endTime) &&
            date('Y-m', $time) === date('Y-m', $endTime) && false !== date('Y-m', $time)) {
            $border = date('j', $endTime);
        }
        return $border;
    }
}