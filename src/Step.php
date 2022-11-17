<?php

namespace markhuot\workflow;

abstract class Step
{
    /** @var array{string, <class-string>} */
    static array $casts = [];

    protected Job $job;

    function setJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }

    function getJob()
    {
        return $this->job;
    }

    function run()
    {
        // no-op
    }

    function finish()
    {
        // no-op
    }

    function getOutput()
    {
        return null;
    }
}
