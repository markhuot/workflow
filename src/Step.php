<?php

namespace markhuot\workflow;

abstract class Step
{
    /** @var array{string, <class-string>} */
    static array $casts = [];

    function run()
    {
        // no-op
    }

    function getOutput()
    {
        return null;
    }
}
