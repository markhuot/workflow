<?php

namespace markhuot\workflow\mutations;

use markhuot\workflow\casts\DefaultInput;
use markhuot\workflow\casts\Expression;
use markhuot\workflow\Step;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Reduce extends Step
{
    static array $casts = [
        'expression' => [DefaultInput::class, Expression::class],
    ];

    function __construct(
        protected string $expression,
    ) {}

    function run()
    {
        // no-op
    }

    function getOutput()
    {
        return $this->expression;
    }
}
