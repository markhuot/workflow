<?php

namespace markhuot\workflow\logging;

class Log
{
    function __construct(
        protected string $message
    ){}

    function run()
    {
        error_log($this->message);
    }
}
