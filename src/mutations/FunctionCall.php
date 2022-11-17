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
        protected mixed $value,
        protected ?array $args=null,
    ) {}

    function run() {
        $function = $this->function;
        if (!empty($this->args)) {
            foreach ($this->args as $arg) {

            }
        }
        else {
            $this->value = $function($this->value);
        }
    }

    function getOutput()
    {
        return $this->value;
    }
}
