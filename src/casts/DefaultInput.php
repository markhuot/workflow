<?php

namespace markhuot\workflow\casts;

use markhuot\workflow\Job;

class DefaultInput
{
    function __invoke(Job $job, $value)
    {
        if (empty($value)) {
            return $job->getOutput('$?');
        }

        return $value;
    }
}
