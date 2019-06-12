<?php

namespace Lib\TaskQueue\Lib;

/**
 * 任务队列配置文件
 * User: dbn
 * Date: 2017/9/28
 * Time: 17:59
 */
class TaskQueueConfig {

    /**
     * 任务分发主队列
     */
    const REDIS_TASK_MAIN_LIST        = 'taskQueueMain';

    /**
     * 消费者队列，不可重复，每个值使用“,”分割，每个值代表一个处理线程
     * 每建立一个队列要在TaskCron创建对应的调用程序，删除修改亦然
     * -注意-》删除或修改已存在的队列，可能会使所操作的队列中的任务数据丢失，新产生的数据会分配到配置中存在的队列
     */
    //todo: 数据量大时候需要进行数据持久化处理，备份数据，目前没数据备份相关处理
    const REDIS_TASK_CONSUMER_LIST    = 'taskQueueConsumer1,taskQueueConsumer2';

    /**
     * 哈希节点数量最低限度，每个消费者队列会创建一个主节点。主节点数量不足会创建虚拟节点映射到主节点上，以满足最低限度
     * 为尽量均衡分配：当消费队列为一个时，数量 = 0。当消费队列大于一个时候，数量 >= 100，数量不易过大影响系统开销
     */
    const REDIS_TASK_VIRTUAL_NODE_MIN = 100;

    /**
     * 单个任务允许最大执行次数
     */
    const REDIS_TASK_EXEC_MAX         = 3;

    /**
     * 单个任务执行结果保存有效时间，单位：分钟
     */
    const REDIS_TASK_RESULT_TIME      = 30;

    /**
     * 任务执行结果
     */
    const TASK_RESULT_FAIL            = 0; // 失败
    const TASK_RESULT_SUCCESS         = 1; // 成功
    const TASK_RESULT_NULL            = 2; // 未查询到结果（处理中或者已过期）

    /**
     * 话题绑定Handle
     */
    public static $_topic = array(
        'orderStat'     => array('OrderAmount'), // 订单统计话题
        'apiStat'       => array('ApiAmount'),//开放api统计话题
        'transportStat' => array('TransportAmount'),//运输单统计
        'TranContract'  => array('CreateTranContract'),//生成订单运输合同
    );
}