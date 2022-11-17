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
        protected array|string|null $map=null,
    ) {}

    function run()
    {
        $mappedItems = [];

        foreach ($this->items as $key => $item) {
            if (is_string($this->map)) {
                $mappedItems[$key] = (new ExpressionLanguage())->evaluate($this->map, ['item' => $item]);
            }

            else if (isset($this->map[0])) {
                $mappedItems[$key] = (new Workflow([
                    'env' => ['item' => $item],
                    'jobs' => [
                        'default' => [
                            'steps' => $this->map
                        ],
                    ]]))
                    ->runJob('default')
                    ->getOutput('$?');
            }

            else if (is_array($this->map)) {
                foreach ($this->map as $subKey => $transformer) {
                    if (is_string($transformer)) {
                        $mappedItems[$key][$subKey] = (new ExpressionLanguage())->evaluate($transformer, ['item' => $item]);
                        continue;
                    }

                    $mappedItems[$key][$subKey] = (new Workflow(['env' => ['item' => $item], 'jobs' => ['default' => ['steps' => $transformer]]]))
                        ->runJob('default')
                        ->getOutput('$?');
                }
            }
        }
        
        $this->items = $mappedItems;
    }

    function getOutput()
    {
        return $this->items;
    }
}
