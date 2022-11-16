<?php

namespace markhuot\workflow\fetchers;

use markhuot\workflow\Step;
use Symfony\Component\DomCrawler\Crawler;

class Html extends Step
{
    protected iterable $nodes = [];

    function __construct(
        protected string $selector,
        protected mixed $url=null,
        protected ?string $html=null,
    ) {}

    function run()
    {
        $crawler = new Crawler($this->html ?? file_get_contents($this->url));
        $this->nodes = $crawler->filter($this->selector);
    }

    function getOutput()
    {
        return $this->nodes;
    }
}
