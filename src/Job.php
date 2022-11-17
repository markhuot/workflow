<?php

namespace markhuot\workflow;

use markhuot\workflow\exceptions\PaginationCompleteException;

class Job
{
    protected int $offset = 0;
    protected array $pagedOutput = [];

    /**
     * @param array{
     *   steps: array{
     *     uses: string
     *   }
     * } $definition
     */
    function __construct(
        protected Workflow $workflow,
        protected array $definition,
    ) {}

    function getEnv()
    {
        return $this->workflow->getEnv();
    }

    function getOutputs()
    {
        return $this->workflow->getOutputs();
    }

    function getOutput($key)
    {
        return $this->workflow->getOutput($key);
    }

    function setOutput($key, $value)
    {
        $this->workflow->setOutput($key, $value);

        return $this;
    }

    function getVariables()
    {
        return array_merge(
            $this->getEnv(),
            $this->getOutputs(),
        );
    }

    /**
     * @return array{
     *     perPage: int,
     *     offset: int,
     * }|null
     */
    function getPagination()
    {
        if (empty($this->definition['paginate'])) {
            return null;
        }

        return array_merge([
            'perPage' => 100,
        ], $this->definition['paginate'], [
            'offset' => $this->offset,
            'output' => $this->pagedOutput,
        ]);
    }

    function run()
    {
        while (true) {
            try {
                $this->runSteps();
            }
            catch (PaginationCompleteException $e) {
                break;
            }

            if ($this->getPagination()) {
                $this->pagedOutput[] = $this->workflow->getOutputs();
                $this->workflow->clearOutputs();
                $this->offset += $this->getPagination()['perPage'];
            }
            else {
                break;
            }
        }

        return $this;
    }

    protected function runSteps()
    {
        $steps = [];

        foreach ($this->definition['steps'] as $definition) {
            $uses = $definition['uses'] ?? null;
            $with = $definition['with'] ?? [];

            if ($uses && $uses::$casts) {
                foreach ($uses::$casts as $key => $casts) {
                    if (!is_array($casts)) {
                        $casts = [$casts];
                    }
                    foreach ($casts as $cast) {
                        $with[$key] = (new $cast())($this, ($with[$key] ?? null));
                    }
                }
            }

            if ($uses) {
                $step = (new $uses(...($with)));
                $step->setJob($this);
                $step->run();
                $steps[] = $step;

                if ($output = ($definition['output'] ?? false)) {
                    $this->workflow->setOutput($output, $step->getOutput());
                }
                else {
                    $this->workflow->setOutput('$?', $step->getOutput());
                }
            }

            if ($definition['run'] ?? false) {
                (function ($args) use ($definition) {
                    $variables = $this->getVariables();
                    extract($variables, EXTR_OVERWRITE);
                    extract($args, EXTR_OVERWRITE);
                    $argv[1] = $variables['$?'] ?? null;
                    $run = $definition['run'];
                    $isSingleLine = preg_match('/[\r\n]+/', $run) === 0;
                    if ($isSingleLine) {
                        $result = eval('return '.$run.';');
                    }
                    else {
                        $result = eval($run);
                    }
                    if ($result !== null) {
                        $this->workflow->setOutput('$?', $result);
                    }
                })($with);
            }
        }

        foreach ($steps as $step)  {
            $step->finish();
        }
    }
}
