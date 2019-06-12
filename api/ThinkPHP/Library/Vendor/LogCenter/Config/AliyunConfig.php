<?php

namespace Logger\Config;

/**
 * 日志配置类
 * User: dbn
 * Date: 2018/5/28
 * Time: 14:02
 */
class AliyunConfig implements IConfig
{
    private $_host;
    private $_clientId;
    private $_secret;
    private $_project;
    private $_logstore;
    private $_source;
    private $_lowestLevel;

    public function __construct()
    {
        // ---- 初始化统一设置：默认配置请在此处配置 ----
        
        $this->_host        = C('LOGGER_API');
        $this->_clientId    = C('LOGGER_CLIENT_ID');
        $this->_secret      = C('LOGGER_SECRET');
        $this->_project     = C('LOGGER_PROJECT');
        $this->_logstore    = C('LOGGER_LOGSTORE');
        $this->_source      = '';
        $this->_lowestLevel = C('LOGGER_LOWEST_LEVEL');

        // --------------------------------------
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->_clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * @param string $project
     */
    public function setProject($project)
    {
        $this->_project = $project;
    }

    /**
     * @return string
     */
    public function getLogstore()
    {
        return $this->_logstore;
    }

    /**
     * @param string $logstore
     */
    public function setLogstore($logstore)
    {
        $this->_logstore = $logstore;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @param mixed $lowestLevel
     */
    public function setLowestLevel($lowestLevel)
    {
        $this->_lowestLevel = $lowestLevel;
    }

    /**
     * @return mixed
     */
    public function getLowestLevel()
    {
        return $this->_lowestLevel;
    }
}