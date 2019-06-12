<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 2018/8/7
 * Time: 16:03
 */

namespace Lib;


use Lib\JPush\Client;
use Lib\JPush\Exceptions\APIConnectionException;
use Lib\JPush\Exceptions\APIRequestException;

class JPushTools
{
    //push链接池
    private static $jPushs = array();

    private $client;
    public function __construct($appKey,$secret)
    {
        try {
            $this->client = new Client($appKey, $secret);
        } catch (\Exception $e){

        }
    }

    public static function getInstance($appKey,$secret){
        if($appKey != '') {
            if (!self::$jPushs[md5($appKey)] || self::$jPushs[md5($appKey)] == null) {
                self::$jPushs[md5($appKey)] = new JPushTools($appKey,$secret);

            }
            return self::$jPushs[md5($appKey)];
        }
        return false;

    }

    public function getClientInfo() {
        return $this->client->getAuthStr();
    }

    public function sendAndroidMessage($deviceId,$message,$title,$extra)
    {
        try {
            $push_payload = $this->client->push()
                ->setPlatform('android')
                ->addRegistrationId($deviceId)
                ->setMessage($message,$title,"type",$extra);

            $response = $push_payload->send();
            if($response['http_code'] == 200) {
                return true;
            }
        } catch (APIConnectionException $e) {
            // try something here
            print $e;
        } catch (APIRequestException $e) {
            // try something here
            print $e;
        }
        return false;
    }

    /**
     * 通过别名推送消息
     * @param $deviceId
     * @param $message
     * @param $title
     * @param $extra
     * @return bool
     */
    public function sendMessageByAlias($deviceId,$message,$title,$extra=null)
    {
        try {
            $push_payload = $this->client->push()
                ->setPlatform('android')
                ->addAlias($deviceId)
                ->setMessage($message,$title,"type",$extra);

            $response = $push_payload->send();
            if($response['http_code'] == 200) {
                return true;
            }
        } catch (APIConnectionException $e) {
            // try something here
            return false;
        } catch (APIRequestException $e) {
            // try something here
            return false;
        }
        return false;
    }

}