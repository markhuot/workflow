<?php

namespace markhuot\workflow\fetchers;

use markhuot\workflow\exceptions\PaginationCompleteException;
use markhuot\workflow\Step;

function generated($callback, $perPage=null, $limit=null, $offset=null)
{
    $perPageOffset = 0;

    while (true) {
        $suffix = '';
        if ($perPage) {
            $suffix = 'limit ' . $perPage . ' offset '.$perPageOffset;
        }
        if ($limit) {
            $suffix = 'limit ' . $limit . ' offset '.$offset;
        }
        $rows = $callback($suffix);

        if ($limit !== null && $offset !== null && empty($rows)) {
            throw new PaginationCompleteException;
        }

        if (empty($rows)) {
            break;
        }

        foreach ($rows as $row) {
            yield $row;
        }

        if ($perPage) {
            $perPageOffset += $perPage;
        }
        else {
            break;
        }
    }
}

class Mysql extends Step
{
    protected ?\PDO $connection;
    protected \Generator|array|null $result;

    function __construct(
        protected string $dsn,
        protected ?string $username,
        protected ?string $password,
        protected string $query,
        protected array $options=[],
        protected ?int $perPage=null,
        protected ?int $limit=null,
    ) {}

    function run()
    {
        $limit = $this->limit ?? $this->getJob()->getPagination()['perPage'] ?? null;
        $offset = $this->getJob()->getPagination()['offset'] ?? null;

        $this->connection = new \PDO($this->dsn, $this->username, $this->password, $this->options);
        $this->result = generated(function ($suffix) {
            //echo "Running: ".$this->query.' '.$suffix."\n";
            return $this->connection->query($this->query.' '.$suffix)->fetchAll(\PDO::FETCH_ASSOC);
        }, perPage: $this->perPage, limit: $limit, offset: $offset);
    }

    function getOutput()
    {
        return $this->result;
    }

    function finish()
    {
        // canonize the current generator in to the result so it can persist after the connection
        // is closed
        $this->result = $this->result->current();
        $this->connection = null;
    }
}
