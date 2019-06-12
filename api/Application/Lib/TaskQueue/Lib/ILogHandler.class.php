<?php

namespace Lib\TaskQueue\Lib;

interface ILogHandler
{
    public function write($msg);
}