<?php
/**
 * Created by PhpStorm.
 * User: Thinkpad
 * Date: 2016/4/18
 * Time: 12:15
 */

namespace TCT;



interface ILogHandler
{
    public function write($msg);

}

class LogHandler implements ILogHandler
{
    private $handle = null;

    public function __construct($file = '')
    {
        $this->handle = fopen($file,'a');
    }

    public function write($msg)
    {
        fwrite($this->handle, $msg, 4096);
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

}