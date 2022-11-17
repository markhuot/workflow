<?php

namespace markhuot\workflow;

class Workflow
{
    /** @var array{string: mixed} */
    protected array $output = [];

    function __construct(
        protected array $definition
    ) {}

    function getName()
    {
        return $this->definition['name'] ?? ((new \ReflectionClass($this))->getShortName());
    }

    function getEnv()
    {
        return $this->definition['env'] ?? [];
    }

    function setOutput($key, $value)
    {
        $this->output[$key] = $value;

        return $this;
    }

    function clearOutputs()
    {
        $this->output = [];

        return $this;
    }

    function getOutputs()
    {
        return $this->output;
    }

    function getOutput($key)
    {
        return $this->output[$key] ?? null;
    }

    function getTrigger($trigger)
    {
        $value = $this->definition['trigger'][$trigger] ?? [];
        return !is_array($value) ? [$value] : $value;
    }

    function trigger($trigger)
    {
        $jobs = $this->getTrigger($trigger);

        foreach ($jobs as $jobName)
        {
            $this->runJob($jobName);
        }
    }

    function getJob($name)
    {
        return new Job($this, $this->definition['jobs'][$name] ?? []);
    }

    function runJob($name)
    {
        $this->getJob($name)->run();

        return $this;
    }
}
