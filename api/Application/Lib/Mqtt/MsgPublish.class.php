<?php

namespace Lib\Mqtt;

class MsgPublish{

	const QOS_MOST_ONCE 	= 0;//最多一次，有可能重复或丢失
	const QOS_LEAST_ONCE 	= 1;//最少一次，
	const QOS_ONLY_ONCE		= 2;//有且只有一次

	const MSG_RETAIN		= 1;//消息保持
	const MSG_NORMAL		= 0;//不保存消息

	/**
	 * Msg 实体
	 */
	protected static $instance;

	/**
	 * 是否已连接MQTT服务器
	*/
	private $_connect = false;

	public $error = array();

	public function __construct()
	{
		$this->mqtt = new MQTT(C('MQTT_HOST'), C('MQTT_HOST_PORT'),'clientID_'.time().rand(0,100));
	}

	public static function getInstance(){
		if(!isset(self::$instance)){
			self::$instance = new MsgPublish();
		}
		return self::$instance;
	}
	public function Connect(){
		$this->_connect = $this->mqtt->connect();
		if(!$this->_connect)
			$this->error[] = 'MQTT connect failed';
		return $this->_connect;
	}
	public function DisConnect(){
		$this->mqtt->disconnect();
		$this->_connect = false;
	}
	public function sendMsg($topic,$content){

		 if(!$this->_connect){
			 $this->Connect();
		 }
		 $this->mqtt->publish($topic,$content,MsgPublish::QOS_MOST_ONCE,MsgPublish::MSG_NORMAL);
	}
}


?>
