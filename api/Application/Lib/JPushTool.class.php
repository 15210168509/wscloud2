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

class JPushTool
{
    private static $jPush;
    private $client;
    public function __construct()
    {
        try {
            $this->client = new Client(C('JPUSH_APP_KEY'), C('JPUSH_MASTER_SECRET'));
        } catch (\Exception $e){

        }
    }

    public static function getInstance(){
        if (!self::$jPush){
            self::$jPush = new JPushTool();
        }
        return self::$jPush;
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