<?php

namespace Lib\LoginLog;

/**
 * Redis登录日志处理类
 * User: dbn
 * Date: 2017/10/11
 * Time: 15:53
 */
class LoginLogRedisHandle extends LoginLogCommon
{
    /**
     * 记录登录：每天只记录一次登录，只允许设置当月内登录记录
     * @param  string $key  日志记录Key
     * @param  int    $time 时间戳
     * @return boolean
     */
    public function setLogging($key, $time)
    {
        if ($this->checkLoginLogKey($key) && $this->checkTimeWhetherThisMonth($time)) {

            // 判断用户当天是否已经登录过
            $whetherLoginResult = $this->dateWhetherLogin($key, $time);
            if (!$whetherLoginResult) {

                // 当天未登录，记录登录
                $this->_redis->setBit($key, date('d', $time), 1);
            }
            return true;
        }
        return false;
    }

    /**
     * 从Redis中判断用户在某一天是否登录过
     * @param  string $key  日志记录Key
     * @param  int    $time 时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function dateWhetherLogin($key, $time)
    {
        if ($this->checkLoginLogKey($key) && $this->checkTime($time)) {
            $result = $this->_redis->getBit($key, date('d', $time));
            if ($result === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * 从Redis中判断用户在某月是否登录过
     * @param  string $key  日志记录Key
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function dateMonthWhetherLogin($key)
    {
        if ($this->checkLoginLogKey($key)) {
            $result = $this->_redis->bitCount($key);
            if ($result > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断某月登录记录在Redis中是否存在
     * @param  string  $key  日志记录Key
     * @return boolean
     */
    public function checkRedisLogExists($key)
    {
        if ($this->checkLoginLogKey($key)) {
            if ($this->_redis->exists($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 从Redis中获取用户某月记录在指定时间范围内的用户信息
     * @param  string  $key       日志记录Key
     * @param  int     $startTime 开始时间戳
     * @param  int     $endTime   结束时间戳
     * @return array
     * array(
     *      'hasLog' => array(
     *          'count' => n,                                  // 有效登录次数，每天重复登录算一次
     *          'list' => array('2017-10-1', '2017-10-15' ...) // 有效登录日期
     *      ),
     *      'notLog' => array(
     *          'count' => n,                                  // 未登录次数
     *          'list' => array('2017-10-1', '2017-10-15' ...) // 未登录日期
     *      )
     * )
     */
    public function getUserTimeRangeLogin($key, $startTime, $endTime)
    {
        $hasCount = 0;       // 有效登录次数
        $notCount = 0;       // 未登录次数
        $hasList  = array(); // 有效登录日期
        $notList  = array(); // 未登录日期

        if ($this->checkLoginLogKey($key) && $this->checkTimeRange($startTime, $endTime) && $this->checkRedisLogExists($key)) {

            $keyTime = $this->getLoginLogKeyInfo($key, 'time');
            $keyTime = strtotime($keyTime);
            $timeYM  = date('Y-m', $keyTime);

            // 设置开始时间
            $initDay = $this->getStartTimeBorder($keyTime, $startTime);

            // 设置结束时间
            $border = $this->getEndTimeBorder($keyTime, $endTime);

            for ($i = $initDay; $i <= $border; $i++) {
                $result = $this->_redis->getBit($key, $i);
                if ($result === 1) {
                    $hasCount++;
                    $hasList[] = $timeYM . '-' . $this->setDateLeadingZero($i);
                } else {
                    $notCount++;
                    $notList[] = $timeYM . '-' . $this->setDateLeadingZero($i);
                }
            }
        }

        return array(
            'hasLog' => array(
                'count' => $hasCount,
                'list'  => $hasList
            ),
            'notLog' => array(
                'count' => $notCount,
                'list'  => $notList
            )
        );
    }

    /**
     * 面向用户：获取时间范围内可能需要的Key
     * @param  string $type      用户类型
     * @param  int    $uid       唯一标识（用户ID）
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return array
     */
    public function getTimeRangeRedisKey($type, $uid, $startTime, $endTime)
    {
        $list = array();

        if ($this->checkType($type) && $this->checkUid($uid) && $this->checkTimeRange($startTime, $endTime)) {

            $data = $this->getSpecifyUserKeyHandle($type, $uid, $startTime);
            if (!empty($data)) { $list[] = $data; }

            $temYM  = strtotime('+1 month', strtotime(date('Y-m', $startTime)));

            while ($temYM <= $endTime) {
                $data = $this->getSpecifyUserKeyHandle($type, $uid, $temYM);
                if (!empty($data)) { $list[] = $data; }

                $temYM  = strtotime('+1 month', $temYM);
            }
        }
        return $list;
    }
    private function getSpecifyUserKeyHandle($type, $uid, $time)
    {
        $data = array();
        $key = $this->getLoginLogKey($type, $uid, $time);
        if ($this->checkLoginLogKey($key)) {
            $data = array(
                'key'  => $key,
                'time' => $time
            );
        }
        return $data;
    }

    /**
     * 面向类型：获取时间范围内可能需要的Key
     * @param  string $type      用户类型
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return array
     */
    public function getSpecifyTypeTimeRangeRedisKey($type, $startTime, $endTime)
    {
        $list = array();

        if ($this->checkType($type) && $this->checkTimeRange($startTime, $endTime)) {

            $data = $this->getSpecifyTypeKeyHandle($type, $startTime);
            if (!empty($data)) { $list[] = $data; }

            $temYM  = strtotime('+1 month', strtotime(date('Y-m', $startTime)));

            while ($temYM <= $endTime) {
                $data = $this->getSpecifyTypeKeyHandle($type, $temYM);
                if (!empty($data)) { $list[] = $data; }

                $temYM  = strtotime('+1 month', $temYM);
            }
        }
        return $list;
    }
    private function getSpecifyTypeKeyHandle($type, $time)
    {
        $data = array();
        $temUid = '11111111';

        $key = $this->getLoginLogKey($type, $temUid, $time);
        if ($this->checkLoginLogKey($key)) {
            $arr = explode('_', $key);
            $arr[count($arr)-1] = '*';
            $key = implode('_', $arr);
            $data = array(
                'key'  => $key,
                'time' => $time
            );
        }
        return $data;
    }

    /**
     * 面向全部：获取时间范围内可能需要的Key
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return array
     */
    public function getSpecifyAllTimeRangeRedisKey($startTime, $endTime)
    {
        $list = array();

        if ($this->checkTimeRange($startTime, $endTime)) {

            $data = $this->getSpecifyAllKeyHandle($startTime);
            if (!empty($data)) { $list[] = $data; }

            $temYM  = strtotime('+1 month', strtotime(date('Y-m', $startTime)));

            while ($temYM <= $endTime) {
                $data = $this->getSpecifyAllKeyHandle($temYM);
                if (!empty($data)) { $list[] = $data; }

                $temYM  = strtotime('+1 month', $temYM);
            }
        }
        return $list;
    }
    private function getSpecifyAllKeyHandle($time)
    {
        $data = array();
        $temUid  = '11111111';
        $temType = 'office';

        $key = $this->getLoginLogKey($temType, $temUid, $time);
        if ($this->checkLoginLogKey($key)) {
            $arr = explode('_', $key);
            array_pop($arr);
            $arr[count($arr)-1] = '*';
            $key = implode('_', $arr);
            $data = array(
                'key'  => $key,
                'time' => $time
            );
        }
        return $data;
    }

    /**
     * 从Redis中查询满足条件的Key
     * @param  string $key 查询的Key
     * @return array
     */
    public function getKeys($key)
    {
        return $this->_redis->keys($key);
    }

    /**
     * 从Redis中删除记录
     * @param  string $key 记录的Key
     * @return boolean
     */
    public function delLoginLog($key)
    {
        return $this->_redis->del($key);
    }

    /**
     * 获取日志标准Key：前缀_年-月_用户类型_唯一标识
     * @param  string $type 用户类型
     * @param  int    $uid  唯一标识（用户ID）
     * @param  int    $time 时间戳
     * @return string
     */
    public function getLoginLogKey($type, $uid, $time)
    {
        if ($this->checkType($type) && $this->checkUid($uid) && $this->checkTime($time)) {
            return 'loginLog_' . date('Y-m', $time) . '_' . $type . '_' . $uid;
        }
        return '';
    }

    /**
     * 获取日志标准Key上信息
     * @param  string $key   key
     * @param  string $field 需要的参数 time,type,uid
     * @return mixed 返回对应的值，没有返回null
     */
    public function getLoginLogKeyInfo($key, $field)
    {
        $param = array();
        if ($this->checkLoginLogKey($key)) {
            $arr = explode('_', $key);
            $param['time'] = $arr[1];
            $param['type'] = $arr[2];
            $param['uid']  = $arr[3];
        }
        return $param[$field];
    }

    /**
     * 获取Key记录的登录位图
     * @param  string $key key
     * @return string
     */
    public function getLoginLogBitMap($key)
    {
        $bitMap = '';
        if ($this->checkLoginLogKey($key)) {
            $time = $this->getLoginLogKeyInfo($key, 'time');
            $maxDay = $this->getDaysInMonth(strtotime($time));
            for ($i = 1; $i <= $maxDay; $i++) {
                $bitMap .= $this->_redis->getBit($key, $i);
            }
        }
        return $bitMap;
    }

    /**
     * 验证日志标准Key
     * @param  string $key
     * @return boolean
     */
    public function checkLoginLogKey($key)
    {
        return parent::checkLoginLogKey($key);
    }

    /**
     * 验证开始/结束时间
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return boolean
     */
    public function checkTimeRange($startTime, $endTime)
    {
        return parent::checkTimeRange($startTime, $endTime);
    }

    /**
     * 验证用户类型
     * @param  string $type
     * @return boolean
     */
    public function checkType($type)
    {
        return parent::checkType($type);
    }

    /**
     * 验证过期时间
     * @param  int $existsDay 一条记录在Redis中过期时间，单位：天，必须大于31
     * @return boolean
     */
    public function checkExistsDay($existsDay)
    {
        return parent::checkExistsDay($existsDay);
    }
}