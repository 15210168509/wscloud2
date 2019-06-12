<?php

namespace Lib\TaskQueue\Lib;

/**
 * 一致性哈希算法相关操作
 * User: dbn
 * Date: 2017/9/28
 * Time: 17:06
 */

final class ConsistentHash {

    // server列表，创建的虚拟节点也会存储在此中
    private $_server_list = array();

    // 每次实例化保存配置信息
    private static $_history_config_server = array();

    // 延迟排序，排序标识每次添加Server后需要重新排序
    private $_layze_sorted = false;

    private static $instance;

    private function __clone(){}
    private function __construct()
    {

        // 获取server
        $serverList = self::getServerConfig();

        if (!empty($serverList)) {

            // 为每个Server创建虚拟节点
            foreach ($serverList as $key => $val) {
                $this->addServer($val);

                // 计算虚拟节点数量
                $cLen = count($serverList);
                $min  = TaskQueueConfig::REDIS_TASK_VIRTUAL_NODE_MIN;

                if ($cLen < $min) {
                    $num = floor($min / $cLen);
                } else {
                    $num = $cLen;
                }

                // 创建虚拟节点
                for ($i = 1; $i <= $num; $i++) {
                    $this->addServer($val.'#'.$i);
                }
            }
        }
    }

    /**
     * 获取当前类的实例化对象
     * @return ConsistentHash
     */
    public static function getInstance()
    {

        // 获取server配置
        $serverList = self::getServerConfig();
        $diff = array_diff($serverList, self::$_history_config_server);

        if (!(self::$instance instanceof self) || count($diff) > 0) {
            self::$instance = new self();
            self::$_history_config_server = $serverList;
        }
        return self::$instance;
    }

    /**
     * 获取分配的存储Server
     * @param  string $key 请求数据任务ID
     * @return string 分配的Server，无返回''
     */
    public function getSaveServer($key)
    {

        // 排序
        if (!$this->_layze_sorted) {
            ksort($this->_server_list);
            $this->_layze_sorted = true;
        }

        $hash = $this->getHash($key);
        $len  = count($this->_server_list);
        if ($len == 0) {
            return '';
        }

        $keys   = array_keys($this->_server_list);
        $values = array_values($this->_server_list);

        // 判断是否在区间内，则返回第一个server
        if ($hash  < $keys[0] || $hash > $keys[$len - 1]) {
            return $this->getVirtualNodeMapServer($values[0]);
        }

        foreach ($keys as $key => $pos) {
            $next_pos = null;
            if (isset($keys[$key + 1]))
            {
                $next_pos = $keys[$key + 1];
            }
            if (is_null($next_pos)) {
                return $this->getVirtualNodeMapServer($values[$key]);
            }

            // 区间判断，根据一致性哈希算法分配Server
            if ($hash >= $pos && $hash <= $next_pos) {
                if ($hash == $pos) {
                    return $this->getVirtualNodeMapServer($values[$key]);
                }
                return $this->getVirtualNodeMapServer($values[$key + 1]);
            }
        }
    }

    /**
     * 添加server
     * @param  string $serverNode 节点名称
     * @return $this
     */
    private function addServer($serverNode)
    {
        $hash = $this->getHash($serverNode);
        $this->_layze_sorted = false;

        if (!isset($this->_server_list[$hash])) {
            $this->_server_list[$hash] = $serverNode;
        }
        return $this;
    }

    /**
     * hash处理字符串
     * @param  string $str 字符串
     * @return int
     */
    private function getHash($str) {
        // hash(i) = hash(i-1) * 33 + str[i]
        $hash = 0;
        $s    = md5($str);
        $seed = 5;
        $len  = 32;
        for ($i = 0; $i < $len; $i++) {
            // (hash << 5) + hash 相当于 hash * 33
            //$hash = sprintf("%u", $hash * 33) + ord($s{$i});
            //$hash = ($hash * 33 + ord($s{$i})) & 0x7FFFFFFF;
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }

        return $hash & 0x7FFFFFFF;
    }

    /**
     * 获取虚拟节点映射的主节点
     * @param  string $serverNode 节点名称
     * @return string
     */
    private function getVirtualNodeMapServer($serverNode)
    {

        // 判断是否是主节点Server
        $isExist = strpos($serverNode, '#');
        if (false === $isExist) {
            return $serverNode;
        }

        $strArr = explode('#', $serverNode);
        array_pop($strArr);
        return implode('#', $strArr);
    }

    /**
     * 获取配置中消费者队列
     * @return array
     */
    private function getServerConfig()
    {
        return explode(',', TaskQueueConfig::REDIS_TASK_CONSUMER_LIST);
    }
}