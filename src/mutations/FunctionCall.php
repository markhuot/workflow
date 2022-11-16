<?php

namespace markhuot\workflow\mutations;

use markhuot\workflow\casts\DefaultInput;
use markhuot\workflow\casts\Expression;
use markhuot\workflow\Step;

class FunctionCall extends Step
{
    static array $casts = [
        'value' => [DefaultInput::class, Expression::class],
    ];

    function __construct(
        protected string $function,
        protected string $value,
    ) {}

    function run() {
        $function = $this->function;
        $this->value = $function($this->value);
    }

    function getOutput()
    {
        return $this->value;
    }
}
