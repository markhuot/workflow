<?php

namespace markhuot\workflow\logging;

use markhuot\workflow\casts\DefaultInput;
use markhuot\workflow\Step;

class Dd extends Step
{
    static array $casts = [
        'value' => DefaultInput::class,
    ];

    function __construct(
        protected mixed $value,
    ) {}

    function run()
    {
        dd($this->value);
    }
}
