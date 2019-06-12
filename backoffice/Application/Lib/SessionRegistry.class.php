<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2017/7/5
 * Time: 14:36
 */

namespace Lib;


class SessionRegistry extends Registry
{
    private static $instance;
    private $identity;
    private function __construct($identity)
    {
        $this->identity = $identity;
    }
    static function getInstance($identity){
        if (!isset(self::$instance)) {
            self::$instance = new self($identity);
        }
        return self::$instance;
    }

    public function get($key)
    {
        if (isset($_SESSION[$this->identity][$key])) {
            return $_SESSION[$this->identity][$key];
        }
        return null;
    }

    public function set($key, $value)
    {
        $_SESSION[$this->identity][$key] = $value;
    }
}