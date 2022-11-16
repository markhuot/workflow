<?php

namespace markhuot\workflow\mutations;

use markhuot\workflow\casts\DefaultInput;
use markhuot\workflow\casts\Expression;
use markhuot\workflow\Job;
use markhuot\workflow\Step;
use markhuot\workflow\Workflow;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Map extends Step
{
    static array $casts = [
        'items' => [DefaultInput::class, Expression::class],
    ];

    function __construct(
        protected iterable $items,
        protected array|string $map,
    ) {}

    function run()
    {
        $this->items = collect($this->items)
            ->map(function ($item) {
                if (is_string($this->map)) {
                    return (new ExpressionLanguage())->evaluate($this->map, ['item' => $item]);
                }

                return collect($this->map)
                    ->map(function ($transformer) use ($item) {
                        if (is_string($transformer)) {
                            return (new ExpressionLanguage())->evaluate($transformer, ['item' => $item]);
                        }

                        return (new Workflow(['env' => ['item' => $item], 'jobs' => ['default' => ['steps' => $transformer]]]))
                            ->runJob('default')
                            ->getOutput('$?');
                    })
                    ->toArray();
            })
            ->toArray();
    }

    function getOutput()
    {
        return $this->items;
    }
}
