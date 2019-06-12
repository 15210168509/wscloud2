<?php

namespace Lib\LoginLog;
use Lib\CLogFileHandler;
use Lib\HObject;
use Lib\Log;
use Lib\Tools;

/**
 * 登录日志操作类
 * User: dbn
 * Date: 2017/10/11
 * Time: 12:01
 * ------------------------
 * 日志最小粒度为：天
 */

class LoginLog extends HObject
{
    private $_redisHandle; // Redis登录日志处理
    private $_dbHandle;    // 数据库登录日志处理

    public function __construct()
    {
        $this->_redisHandle = new LoginLogRedisHandle($this);
        $this->_dbHandle    = new LoginLogDBHandle($this);

        // 初始化日志
        $logHandler = new CLogFileHandler(__DIR__ . '/Logs/del.log');
        Log::Init($logHandler, 15);
    }

    /**
     * 记录登录：每天只记录一次登录，只允许设置当月内登录记录
     * @param  string $type 用户类型
     * @param  int    $uid  唯一标识（用户ID）
     * @param  int    $time 时间戳
     * @return boolean
     */
    public function setLogging($type, $uid, $time)
    {
        $key = $this->_redisHandle->getLoginLogKey($type, $uid, $time);
        if ($this->_redisHandle->checkLoginLogKey($key)) {
            return $this->_redisHandle->setLogging($key, $time);
        }
        return false;
    }

    /**
     * 查询用户某一天是否登录过
     * @param  string $type 用户类型
     * @param  int    $uid  唯一标识（用户ID）
     * @param  int    $time 时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function getDateWhetherLogin($type, $uid, $time)
    {
        $key = $this->_redisHandle->getLoginLogKey($type, $uid, $time);
        if ($this->_redisHandle->checkLoginLogKey($key)) {

            // 判断Redis中是否存在记录
            $isRedisExists = $this->_redisHandle->checkRedisLogExists($key);
            if ($isRedisExists) {

                // 从Redis中进行判断
                return $this->_redisHandle->dateWhetherLogin($key, $time);
            } else {

                // 从数据库中进行判断
                return $this->_dbHandle->dateWhetherLogin($type, $uid, $time);
            }
        }
        return false;
    }

    /**
     * 查询用户某月是否登录过
     * @param  string $type 用户类型
     * @param  int    $uid  唯一标识（用户ID）
     * @param  int    $time 时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function getDateMonthWhetherLogin($type, $uid, $time)
    {
        $key = $this->_redisHandle->getLoginLogKey($type, $uid, $time);
        if ($this->_redisHandle->checkLoginLogKey($key)) {

            // 判断Redis中是否存在记录
            $isRedisExists = $this->_redisHandle->checkRedisLogExists($key);
            if ($isRedisExists) {

                // 从Redis中进行判断
                return $this->_redisHandle->dateMonthWhetherLogin($key);
            } else {

                // 从数据库中进行判断
                return $this->_dbHandle->dateMonthWhetherLogin($type, $uid, $time);
            }
        }
        return false;
    }

    /**
     * 查询用户在某个时间段是否登录过
     * @param  string $type 用户类型
     * @param  int    $uid  唯一标识（用户ID）
     * @param  int    $startTime 开始时间戳
     * @param  int    $endTime   结束时间戳
     * @return boolean 参数错误或未登录过返回false，登录过返回true
     */
    public function getTimeRangeWhetherLogin($type, $uid, $startTime, $endTime){
        $result = $this->getUserTimeRangeLogin($type, $uid, $startTime, $endTime);
        if ($result['hasLog']['count'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 获取用户某时间段内登录信息
     * @param  string $type      用户类型
     * @param  int    $uid       唯一标识（用户ID）
     * @param  int    $startTime 开始时间戳
     * @param  int    $endTime   结束时间戳
     * @return array  参数错误或未查询到返回array()
     * -------------------------------------------------
     * 查询到结果：
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
    public function getUserTimeRangeLogin($type, $uid, $startTime, $endTime)
    {
        $hasCount   = 0;       // 有效登录次数
        $notCount   = 0;       // 未登录次数
        $hasList    = array(); // 有效登录日期
        $notList    = array(); // 未登录日期
        $successFlg = false;   // 查询到数据标识

        if ($this->checkTimeRange($startTime, $endTime)) {

            // 获取需要查询的Key
            $keyList = $this->_redisHandle->getTimeRangeRedisKey($type, $uid, $startTime, $endTime);

            if (!empty($keyList)) {
                foreach ($keyList as $key => $val) {

                    // 判断Redis中是否存在记录
                    $isRedisExists = $this->_redisHandle->checkRedisLogExists($val['key']);
                    if ($isRedisExists) {

                        // 存在，直接从Redis中获取
                        $logInfo = $this->_redisHandle->getUserTimeRangeLogin($val['key'], $startTime, $endTime);
                    } else {

                        // 不存在，尝试从数据库中读取
                        $logInfo = $this->_dbHandle->getUserTimeRangeLogin($type, $uid, $val['time'], $startTime, $endTime);
                    }

                    if (is_array($logInfo)) {
                        $hasCount += $logInfo['hasLog']['count'];
                        $hasList = array_merge($hasList, $logInfo['hasLog']['list']);
                        $notCount += $logInfo['notLog']['count'];
                        $notList = array_merge($notList, $logInfo['notLog']['list']);
                        $successFlg = true;
                    }
                }
            }
        }

        if ($successFlg) {
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

        return array();
    }

    /**
     * 获取某段时间内有效登录过的用户 统一接口
     * @param  int    $startTime 开始时间戳
     * @param  int    $endTime   结束时间戳
     * @param  array  $typeArr   用户类型，为空时获取全部类型
     * @return array  参数错误或未查询到返回array()
     * -------------------------------------------------
     * 查询到结果：指定用户类型
     * array(
     *      'type1' => array(
     *          'count' => n,                     // type1 有效登录总用户数
     *          'list' => array('111', '222' ...) // type1 有效登录用户
     *      ),
     *      'type2' => array(
     *          'count' => n,                     // type2 有效登录总用户数
     *          'list' => array('333', '444' ...) // type2 有效登录用户
     *      )
     * )
     * -------------------------------------------------
     * 查询到结果：未指定用户类型，全部用户，固定键 'all'
     * array(
     *      'all' => array(
     *          'count' => n,                     // 有效登录总用户数
     *          'list' => array('111', '222' ...) // 有效登录用户
     *      )
     * )
     */
    public function getOrientedTimeRangeLogin($startTime, $endTime, $typeArr = array())
    {
        if ($this->checkTimeRange($startTime, $endTime)) {

            // 判断是否指定类型
            if (is_array($typeArr) && !empty($typeArr)) {

                // 指定类型，验证类型合法性
                if ($this->checkTypeArr($typeArr)) {

                    // 依据类型获取
                    return $this->getSpecifyTypeTimeRangeLogin($startTime, $endTime, $typeArr);
                }
            } else {

                // 未指定类型，统一获取
                return $this->getSpecifyAllTimeRangeLogin($startTime, $endTime);
            }
        }
        return array();
    }

    /**
     * 指定类型：获取某段时间内登录过的用户
     * @param  int    $startTime 开始时间戳
     * @param  int    $endTime   结束时间戳
     * @param  array  $typeArr   用户类型
     * @return array
     */
    private function getSpecifyTypeTimeRangeLogin($startTime, $endTime, $typeArr)
    {
        $data = array();
        $successFlg = false; // 查询到数据标识

        // 指定类型，根据类型单独获取，进行整合
        foreach ($typeArr as $typeArrVal) {

            // 获取需要查询的Key
            $keyList = $this->_redisHandle->getSpecifyTypeTimeRangeRedisKey($typeArrVal, $startTime, $endTime);
            if (!empty($keyList)) {

                $data[$typeArrVal]['count'] = 0;       // 该类型下有效登录用户数
                $data[$typeArrVal]['list']  = array(); // 该类型下有效登录用户

                foreach ($keyList as $keyListVal) {

                    // 查询Kye，验证Redis中是否存在：此处为单个类型，所以直接看Redis中是否存在该类型Key即可判断是否存在
                    // 存在的数据不需要去数据库中去查看
                    $standardKeyList = $this->_redisHandle->getKeys($keyListVal['key']);
                    if (is_array($standardKeyList) && count($standardKeyList) > 0) {

                        // Redis存在
                        foreach ($standardKeyList as $standardKeyListVal) {

                            // 验证该用户在此时间段是否登录过
                            $redisCheckLogin = $this->_redisHandle->getUserTimeRangeLogin($standardKeyListVal, $startTime, $endTime);
                            if ($redisCheckLogin['hasLog']['count'] > 0) {

                                // 同一个用户只需记录一次
                                $uid = $this->_redisHandle->getLoginLogKeyInfo($standardKeyListVal, 'uid');
                                if (!in_array($uid, $data[$typeArrVal]['list'])) {
                                    $data[$typeArrVal]['count']++;
                                    $data[$typeArrVal]['list'][] = $uid;
                                }
                                $successFlg = true;
                            }
                        }

                    } else {

                        // 不存在，尝试从数据库中获取
                        $dbResult = $this->_dbHandle->getTimeRangeLoginSuccessUser($keyListVal['time'], $startTime, $endTime, $typeArrVal);
                        if (!empty($dbResult)) {
                            foreach ($dbResult as $dbResultVal) {
                                if (!in_array($dbResultVal, $data[$typeArrVal]['list'])) {
                                    $data[$typeArrVal]['count']++;
                                    $data[$typeArrVal]['list'][] = $dbResultVal;
                                }
                            }
                            $successFlg = true;
                        }
                    }
                }
            }
        }

        if ($successFlg) { return $data; }
        return array();
    }

    /**
     * 全部类型：获取某段时间内登录过的用户
     * @param  int    $startTime 开始时间戳
     * @param  int    $endTime   结束时间戳
     * @return array
     */
    private function getSpecifyAllTimeRangeLogin($startTime, $endTime)
    {
        $count      = 0;       // 有效登录用户数
        $list       = array(); // 有效登录用户
        $successFlg = false;   // 查询到数据标识

        // 未指定类型，直接对所有数据进行检索
        // 获取需要查询的Key
        $keyList = $this->_redisHandle->getSpecifyAllTimeRangeRedisKey($startTime, $endTime);

        if (!empty($keyList)) {
            foreach ($keyList as $keyListVal) {

                // 查询Kye
                $standardKeyList = $this->_redisHandle->getKeys($keyListVal['key']);

                if (is_array($standardKeyList) && count($standardKeyList) > 0) {

                    // 查询到Key，直接读取数据，记录类型
                    foreach ($standardKeyList as $standardKeyListVal) {

                        // 验证该用户在此时间段是否登录过
                        $redisCheckLogin = $this->_redisHandle->getUserTimeRangeLogin($standardKeyListVal, $startTime, $endTime);
                        if ($redisCheckLogin['hasLog']['count'] > 0) {

                            // 同一个用户只需记录一次
                            $uid = $this->_redisHandle->getLoginLogKeyInfo($standardKeyListVal, 'uid');
                            if (!in_array($uid, $list)) {
                                $count++;
                                $list[] = $uid;
                            }
                            $successFlg = true;
                        }
                    }
                }

                // 无论Redis中存在不存在都要尝试从数据库中获取一遍数据，来补充Redis获取的数据，保证检索数据完整（Redis类型缺失可能导致）
                $dbResult = $this->_dbHandle->getTimeRangeLoginSuccessUser($keyListVal['time'], $startTime, $endTime);
                if (!empty($dbResult)) {
                    foreach ($dbResult as $dbResultVal) {
                        if (!in_array($dbResultVal, $list)) {
                            $count++;
                            $list[] = $dbResultVal;
                        }
                    }
                    $successFlg = true;
                }
            }
        }

        if ($successFlg) {
            return array(
                'all' => array(
                    'count' => $count,
                    'list'  => $list
                )
            );
        }
        return array();
    }

    /**
     * 验证开始结束时间
     * @param  string $startTime 开始时间
     * @param  string $endTime   结束时间
     * @return boolean
     */
    private function checkTimeRange($startTime, $endTime)
    {
        return $this->_redisHandle->checkTimeRange($startTime, $endTime);
    }

    /**
     * 批量验证用户类型
     * @param  array  $typeArr 用户类型数组
     * @return boolean
     */
    private function checkTypeArr($typeArr)
    {
        $flg = false;
        if (is_array($typeArr) && !empty($typeArr)) {
            foreach ($typeArr as $val) {
                if ($this->_redisHandle->checkType($val)) {
                    $flg = true;
                } else {
                    $flg = false; break;
                }
            }
        }
        return $flg;
    }

    /**
     * 定时任务每周调用一次：从Redis同步登录日志到数据库
     * @param  int    $existsDay 一条记录在Redis中过期时间，单位：天，必须大于31
     * @return string
     * 'null'：   Redis中无数据
     * 'fail'：   同步失败
     * 'success'：同步成功
     */
    public function cronWeeklySync($existsDay)
    {

        // 验证生存时间
        if ($this->_redisHandle->checkExistsDay($existsDay)) {
            $likeKey = 'loginLog_*';
            $keyList = $this->_redisHandle->getKeys($likeKey);

            if (!empty($keyList)) {
                foreach ($keyList as $keyVal) {

                    if ($this->_redisHandle->checkLoginLogKey($keyVal)) {
                        $keyTime         = $this->_redisHandle->getLoginLogKeyInfo($keyVal, 'time');
                        $thisMonth       = date('Y-m');
                        $beforeMonth     = date('Y-m', strtotime('-1 month'));

                        // 验证是否需要进行同步：
                        // 1. 当前日期 >= 8号，对本月所有记录进行同步，不对本月之前的记录进行同步
                        // 2. 当前日期 <  8号，对本月所有记录进行同步，对本月前一个月的记录进行同步，对本月前一个月之前的所有记录不进行同步
                        if (date('j') >= 8) {

                            // 只同步本月数据
                            if ($thisMonth == $keyTime) {
                                $this->redis2db($keyVal);
                            }
                        } else {

                            // 同步本月或本月前一个月数据
                            if ($thisMonth == $keyTime || $beforeMonth == $keyTime) {
                                $this->redis2db($keyVal);
                            }
                        }

                        // 验证是否过期
                        $existsSecond =  $existsDay * 24 * 60 * 60;
                        if (strtotime($keyTime) + $existsSecond < time()) {

                            // 过期删除
                            $bitMap = $this->_redisHandle->getLoginLogBitMap($keyVal);
                            Log::INFO('删除过期数据[' . $keyVal . ']：' . $bitMap);
                            $this->_redisHandle->delLoginLog($keyVal);
                        }
                    }
                }
                return 'success';
            }
            return 'null';
        }
        return 'fail';
    }

    /**
     * 将记录同步到数据库
     * @param  string $key 记录Key
     * @return boolean
     */
    private function redis2db($key)
    {
        if ($this->_redisHandle->checkLoginLogKey($key) && $this->_redisHandle->checkRedisLogExists($key)) {
            $time = $this->_redisHandle->getLoginLogKeyInfo($key, 'time');
            $data['id']      = Tools::generateId();
            $data['user_id'] = $this->_redisHandle->getLoginLogKeyInfo($key, 'uid');
            $data['type']    = $this->_redisHandle->getLoginLogKeyInfo($key, 'type');
            $data['year']    = date('Y', strtotime($time));
            $data['month']   = date('n', strtotime($time));
            $data['bit_log'] = $this->_redisHandle->getLoginLogBitMap($key);
            return $this->_dbHandle->redis2db($data);
        }
        return false;
    }
}