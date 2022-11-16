<?php

namespace markhuot\workflow\fetchers;

use markhuot\workflow\Step;

function generated($callback, $paginate=false, $perPage=10)
{
    $offset = 0;

    while (true) {
        $rows = $callback($paginate ? 'limit ' . $perPage . ' offset '.$offset : '');
        if (empty($rows)) {
            break;
        }

        foreach ($rows as $row) {
            yield $row;
        }

        if ($paginate) {
            $offset += $perPage;
        }
        else {
            break;
        }
    }
}

class Mysql extends Step
{
    protected \Generator $result;

    function __construct(
        protected string $dsn,
        protected ?string $username,
        protected ?string $password,
        protected string $query,
        protected array $options=[],
        protected bool $paginate=false,
        protected int $perPage=100,
    ) {}

    function run()
    {
        $connection = new \PDO($this->dsn, $this->username, $this->password, $this->options);
        $this->result = generated(function ($suffix) use ($connection) {
            // echo "Running: ".$this->query.' '.$suffix."\n";
            return $connection->query($this->query.' '.$suffix)->fetchAll(\PDO::FETCH_ASSOC);
        }, paginate: $this->paginate, perPage: $this->perPage);
    }

    function getOutput()
    {
        return $this->result;
    }
}
