<?php

namespace markhuot\workflow\casts;

use markhuot\workflow\Job;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Expression
{
    function __invoke(Job $job, $value)
    {
        if (is_string($value)) {
            return (new ExpressionLanguage)->evaluate($value, $job->getVariables());
        }

        return $value;
    }
}
