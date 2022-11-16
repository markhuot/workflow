<?php

namespace markhuot\workflow;

class Job
{
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

    function run()
    {
        foreach ($this->definition['steps'] as $definition) {
            $uses = $definition['uses'];

            $with = $definition['with'] ?? [];

            if ($uses::$casts) {
                foreach ($uses::$casts as $key => $casts) {
                    if (!is_array($casts)) {
                        $casts = [$casts];
                    }
                    foreach ($casts as $cast) {
                        $with[$key] = (new $cast())($this, ($with[$key] ?? null));
                    }
                }
            }

            $step = (new $uses(...($with)));
            $step->run();

            if ($output = ($definition['output'] ?? false)) {
                $this->workflow->setOutput($output, $step->getOutput());
            }
            else {
                $this->workflow->setOutput('$?', $step->getOutput());
            }
        }

        return $this;
    }
}
