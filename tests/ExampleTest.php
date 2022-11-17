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

test('simple maps', function () {
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
                              first_name: item['firstName']
                              last_name: item['lastName']
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe([['first_name' => 'Michael', 'last_name' => 'Bluth']]);
});

test('reducing maps', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      with:
                          data:
                              - firstName: Michael
                                lastName: Bluth
                              - firstName: Gob
                                lastName: Bluth
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map:
                              - uses: markhuot\\workflow\\mutations\\FunctionCall
                                with:
                                    function: strtoupper
                                    value: item['firstName']
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe(['MICHAEL', 'GOB']);
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

test('supports nested maps', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      with:
                          data:
                              - firstName: Michael
                                lastName: Bluth
                                siblings:
                                    - firstName: Gob
                                      lastName: Bluth
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map:
                              first_name: item['firstName']
                              last_name: item['lastName']
                              siblings:
                                  - uses: markhuot\\workflow\\mutations\\Map
                                    with:
                                        items: item['siblings']
                                        map:
                                            first_name: item['firstName']
                                            last_name: item['lastName']
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe([['first_name' => 'Michael', 'last_name' => 'Bluth', 'siblings' => [['first_name' => 'Gob', 'last_name' => 'Bluth']]]]);
});

test('supports string maps', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Raw
                      with:
                          data:
                              - firstName: Michael
                                lastName: Bluth
                              - firstName: Lindsey
                                lastName: Bluth
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map: \"{\\\"name\\\": item['firstName'] ~ ' ' ~ item['lastName']}\"
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe([['name' => 'Michael Bluth'], ['name' => 'Lindsey Bluth']]);
})->only();

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
                          perPage: 1
                          query: select 'foo', 'bar', 'baz' union select 'a', 'b', 'c'
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map:
                              - run: \"\$item\"
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe([
        ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'],
        ['foo' => 'a', 'bar' => 'b', 'baz' => 'c']
    ]);
});

test('uses generator functions', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Mysql
                      with:
                          dsn: mysql:host=localhost;dbname=mysql
                          username: root
                          password:
                          perPage: 1
                          query: select 'foo', 'bar', 'baz' union select 'a', 'b', 'c'
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)
        ->toBeInstanceOf(Generator::class)
        ->current()->toBe(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);
});

test('paginates jobs', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                paginate:
                    perPage: 1
                steps:
                    - uses: markhuot\\workflow\\fetchers\\Mysql
                      with:
                          dsn: mysql:host=localhost;dbname=mysql
                          username: root
                          password:
                          query: select 'foo', 'bar', 'baz' union select 'a', 'b', 'c'
    "));

    $job = (new Workflow($defn))
        ->getJob('myJob')
        ->run();

    expect($job->getPagination()['output'][1]['$?']->current())->toBe(['foo' => 'a', 'bar' => 'b', 'baz' => 'c']);
});

test('evaluates php', function () {
    $defn = Yaml::parse(trim("
        jobs:
            myJob:
                steps:
                    - run: \"['Michael', 'Lindsey', 'Gob', 'Buster']\"
                    - uses: markhuot\\workflow\\mutations\\Map
                      with:
                          map:
                              - run: strtoupper(\$item)
    "));

    $output = (new Workflow($defn))
        ->runJob('myJob')
        ->getOutput('$?');

    expect($output)->toBe(['MICHAEL', 'LINDSEY', 'GOB', 'BUSTER']);
});
