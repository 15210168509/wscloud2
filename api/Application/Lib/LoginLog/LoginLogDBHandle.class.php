<?php

namespace Lib\LoginLog;
use Lib\Logger\Logger;
use Think\Model;

/**
 * 数据库登录日志处理类
 * User: dbn
 * Date: 2017/10/11
 * Time: 13:12
 */
class LoginLogDBHandle extends LoginLogCommon
{

    /**
     * 从数据库中获取用户某月记录在指定时间范围内的用户信息
     * @param  string  $type      用户类型
     * @param  int     $uid       唯一标识（用户ID）
     * @param  int     $time      需要查询月份时间戳
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
    public function getUserTimeRangeLogin($type, $uid, $time, $startTime, $endTime)
    {
        $hasCount = 0;       // 有效登录次数
        $notCount = 0;       // 未登录次数
        $hasList  = array(); // 有效登录日期
        $notList  = array(); // 未登录日期

        if ($this->checkType($type) && $this->checkUid($uid) && $this->checkTimeWithinTimeRange($time, $startTime, $endTime)) {

            $timeYM = date('Y-m', $time);

            // 设置开始时间
            $initDay = $this->getStartTimeBorder($time, $startTime);

            // 设置结束时间
            $border = $this->getEndTimeBorder($time, $endTime);

            $bitMap = $this->getBitMapFind($type, $uid, date('Y', $time), date('n', $time));
            for ($i = $initDay; $i <= $border; $i++) {

                if (!empty($bitMap)) {
                    if ($bitMap[$i-1] == '1') {
                        $hasCount++;
                        $hasList[] = $timeYM . '-' . $this->setDateLeadingZero($i);
                    } else {
                        $notCount++;
                        $notList[] = $timeYM . '-' . $this->setDateLeadingZero($i);
                    }
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
     * 从数据库获取用户某月日志位图
     * @param  string  $type  用户类型
     * @param  int     $uid   唯一标识（用户ID）
     * @param  int     $year  年Y
     * @param  int     $month 月n
     * @return string
     */
    private function getBitMapFind($type, $uid, $year, $month)
    {
        $model = D('Home/StatLoginLog');
        $map['type']    = array('EQ', $type);
        $map['user_id'] = array('EQ', $uid);
        $map['year']    = array('EQ', $year);
        $map['month']   = array('EQ', $month);

        $result = $model->field('bit_log')->where($map)->find();
        if (false !== $result && isset($result['bit_log']) && !empty($result['bit_log'])) {
            return $result['bit_log'];
        }
        return '';
    }

    /**
     * 从数据库中判断用户在某一天是否登录过
     * @param  string  $type  用户类型
     * @param  int     $uid   唯一标识（用户ID）
     * @param  int     $time  时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function dateWhetherLogin($type, $uid, $time)
    {
        if ($this->checkType($type) && $this->checkUid($uid) && $this->checkTime($time)) {

            $timeInfo = getdate($time);
            $bitMap = $this->getBitMapFind($type, $uid, $timeInfo['year'], $timeInfo['mon']);
            if (!empty($bitMap)) {
                if ($bitMap[$timeInfo['mday']-1] == '1') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 从数据库中判断用户在某月是否登录过
     * @param  string  $type  用户类型
     * @param  int     $uid   唯一标识（用户ID）
     * @param  int     $time  时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function dateMonthWhetherLogin($type, $uid, $time)
    {
        if ($this->checkType($type) && $this->checkUid($uid) && $this->checkTime($time)) {

            $timeInfo = getdate($time);
            $userArr = $this->getMonthLoginSuccessUser($timeInfo['year'], $timeInfo['mon'], $type);
            if (!empty($userArr)) {
                if (in_array($uid, $userArr)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取某月所有有效登录过的用户ID
     * @param  int     $year  年Y
     * @param  int     $month 月n
     * @param  string  $type  用户类型，为空时获取全部类型
     * @return array
     */
    public function getMonthLoginSuccessUser($year, $month, $type = '')
    {
        $data = array();
        if (is_numeric($year) && is_numeric($month)) {
            $model = D('Home/StatLoginLog');
            $map['year']    = array('EQ', $year);
            $map['month']   = array('EQ', $month);
            $map['bit_log'] = array('LIKE', '%1%');
            if ($type != '' && $this->checkType($type)) {
                $map['type']    = array('EQ', $type);
            }
            $result = $model->field('user_id')->where($map)->select();
            if (false !== $result && count($result) > 0) {
                foreach ($result as $val) {
                    if (isset($val['user_id'])) {
                        $data[] = $val['user_id'];
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 从数据库中获取某月所有记录在指定时间范围内的用户ID
     * @param  int     $time      查询的时间戳
     * @param  int     $startTime 开始时间戳
     * @param  int     $endTime   结束时间戳
     * @param  string  $type  用户类型，为空时获取全部类型
     * @return array
     */
    public function getTimeRangeLoginSuccessUser($time, $startTime, $endTime, $type = '')
    {
        $data = array();
        if ($this->checkTimeWithinTimeRange($time, $startTime, $endTime)) {

            $timeInfo = getdate($time);

            // 获取满足时间条件的记录
            $model = D('Home/StatLoginLog');
            $map['year']    = array('EQ', $timeInfo['year']);
            $map['month']   = array('EQ', $timeInfo['mon']);
            if ($type != '' && $this->checkType($type)) {
                $map['type']    = array('EQ', $type);
            }

            $result = $model->where($map)->select();
            if (false !== $result && count($result) > 0) {

                // 设置开始时间
                $initDay = $this->getStartTimeBorder($time, $startTime);

                // 设置结束时间
                $border = $this->getEndTimeBorder($time, $endTime);

                foreach ($result as $val) {

                    $bitMap = $val['bit_log'];
                    for ($i = $initDay; $i <= $border; $i++) {

                        if ($bitMap[$i-1] == '1' && !in_array($val['user_id'], $data)) {
                            $data[] = $val['user_id'];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 将数据更新到数据库
     * @param  array $data 单条记录的数据
     * @return boolean
     */
    public function redis2db($data)
    {
        $model = D('Home/StatLoginLog');

        // 验证记录是否存在
        $map['user_id'] = array('EQ', $data['user_id']);
        $map['type']    = array('EQ', $data['type']);
        $map['year']    = array('EQ', $data['year']);
        $map['month']   = array('EQ', $data['month']);

        $count = $model->where($map)->count();
        if (false !== $count && $count > 0) {

            // 存在记录进行更新
            $saveData['bit_log'] = $data['bit_log'];

            if (!$model->create($saveData, Model::MODEL_UPDATE)) {

                $this->_loginLog->setError('同步登录日志-更新记录，创建数据对象失败：' . $model->getError());
                Logger::error('同步登录日志-更新记录，创建数据对象失败：' . $model->getError());
                return false;
            } else {

                $result = $model->where($map)->save();

                if (false !== $result) {
                    return true;
                } else {
                    $this->_loginLog->setError('同步登录日志-更新记录，更新数据失败：' . json_encode($data));
                    Logger::error('同步登录日志-更新记录，更新数据失败：' . json_encode($data));
                    return false;
                }
            }
        } else {

            // 不存在记录插入一条新的记录
            if (!$model->create($data, Model::MODEL_INSERT)) {

                $this->_loginLog->setError('同步登录日志-插入记录，创建数据对象失败：' . $model->getError());
                Logger::error('同步登录日志-插入记录，创建数据对象失败：' . $model->getError());
                return false;
            } else {

                $result = $model->add();

                if (false !== $result) {
                    return true;
                } else {
                    $this->_loginLog->setError('同步登录日志-插入记录，插入数据失败：' . json_encode($data));
                    Logger::error('同步登录日志-插入记录，插入数据失败：' . json_encode($data));
                    return false;
                }
            }
        }
    }
}