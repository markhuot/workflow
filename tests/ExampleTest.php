<?php

use markhuot\workflow\Workflow;
use Symfony\Component\Yaml\Yaml;

test('gets default output', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      with:
                          data: bar
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe('bar');
});

test('gets named output', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      output: foo
                      with:
                          data: bar
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('foo');

    expect($output)->toBe('bar');
});

test('remaps data', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      output: source
                      with:
                          data:
                              - firstName: Michael
                                lastName: Bluth
                    - uses: markhuot\\workflow\\mutations\\Map
                      output: source
                      with:
                          items: source
                          map:
                              first_name: item['firstName']
                              last_name: item['lastName']
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('source');

    expect($output)->toBe([['first_name' => 'Michael', 'last_name' => 'Bluth']]);
});

test('maps with transforms', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      with:
                          data:
                              - firstName: Michael
                                lastName: Bluth
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map:
                              first_name:
                                  - uses: markhuot\\workflow\\mutations\\FunctionCall
                                    with:
                                        function: strtoupper
                                        value: item['firstName']
                              last_name:
                                  - uses: markhuot\\workflow\\mutations\\FunctionCall
                                    with:
                                        function: strtoupper
                                        value: item['lastName']
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe([['first_name' => 'MICHAEL', 'last_name' => 'BLUTH']]);
});

test('gets html', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Html
                      with:
                          html: |
                            <html><body>
                                <div class='item' data-attr='foo'>foo</div>
                                <div class='item' data-attr='bar'>bar</div>
                                <div class='item' data-attr='baz'>baz</div>
                            </body></html>
                          selector: .item
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect(collect($output))
        ->map(fn ($node) => $node->textContent)->toArray()->toBe(['foo', 'bar', 'baz'])
        ->map(fn ($node) => $node->getAttribute('data-attr'))->toArray()->toBe(['foo', 'bar', 'baz']);
});

test('gets mysql data', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Mysql
                      with:
                          dsn: mysql:host=localhost;dbname=mysql
                          username: root
                          password:
                          paginate: true
                          perPage: 1
                          query: select 'foo', 'bar', 'baz' union select 'a', 'b', 'c'
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output->current())->toBe(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);
    $output->next();
    expect($output->current())->toBe(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
    $output->next();
    expect($output->current())->toBeNull();
});
