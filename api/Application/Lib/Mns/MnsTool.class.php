<?php

/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/8/16
 * Time: 14:35
 */
namespace Lib\Mns;
use AliyunMNS\Client;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Requests\PublishMessageRequest;

require_once('mns-autoloader.php');
class MnsTool
{
    private static $mns;
    private $client;

    public function __construct()
    {
        $this->client = new Client(C('endPoint'), C('accessId'), C('accessKey'));
    }

    public static function getInstance(){
        if (!self::$mns){
            self::$mns = new MnsTool();
        }
        return self::$mns;
    }

    /**
     * 创建话题
     * @param $topicName
     * @return bool
     */
    public function createTopic($topicName)
    {
        $request = new CreateTopicRequest($topicName);
        try
        {
            $this->client->createTopic($request);
            return true;
        }
        catch (MnsException $e)
        {
            return false;
        }
    }

    /**
     * 获取话题
     * @param $topicName
     * @return \AliyunMNS\Topic
     */
    public function getTopicRef($topicName)
    {
        return $this->client->getTopicRef($topicName);
    }

    /**
     * 推送消息
     * @param $topicName
     * @param $messageBody
     * @return boolean
     */
    public function publishMessage($topicName,$messageBody)
    {
        $request = new PublishMessageRequest($messageBody);
        $topic = $this->client->getTopicRef($topicName);
        try
        {
            $topic->publishMessage($request);
            return true;
        }
        catch (MnsException $e)
        {
            return false;
        }
    }

    /**
     * 创建订阅
     * @param $topicName
     * @param $subscriptionName
     * @param $url
     * @return boolean
     */
    public function createSubscribe($topicName,$subscriptionName,$url)
    {
        $topic = $this->client->getTopicRef($topicName);
        $attributes = new SubscriptionAttributes($subscriptionName, $url,null,'json');

        try
        {
            $topic->subscribe($attributes);
            return true;
        }
        catch (MnsException $e)
        {
            return false;
        }
    }

    /**
     * 取消订阅
     * @param $topicName
     * @param $subscriptionName
     */
    public function unsubscribe($topicName,$subscriptionName)
    {
        $topic = $this->client->getTopicRef($topicName);
        try
        {
            $topic->unsubscribe($subscriptionName);
            echo "Unsubscribe Succeed! \n";
        }
        catch (MnsException $e)
        {
            echo "Unsubscribe Failed: " . $e;
            return;
        }
    }
}