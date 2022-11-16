<?php

namespace markhuot\workflow\fetchers;

use markhuot\workflow\Step;

class Raw extends Step
{
    function __construct(
        protected mixed $data
    ) {}

    function run()
    {
        // no-op
    }

    function getOutput()
    {
        return $this->data;
    }
}
