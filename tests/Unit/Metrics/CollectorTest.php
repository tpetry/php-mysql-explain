<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Metrics;

use Mockery;
use RuntimeException;
use Tpetry\PhpMysqlExplain\Metrics\Collector;
use Tpetry\PhpMysqlExplain\Queries\QueryInterface;

dataset('results', [
    fn() => [
        'version' => '8.0.34',
        'explain-json' => '{ "query_block": { "select_id": 1, "message": "Impossible WHERE" } }',
        'explain-tree' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)',
    ],
]);

it('executes SQL queries and processes results', function (array $results): void {
    $query = Mockery::mock(QueryInterface::class)
        ->shouldReceive('getSql')->andReturn('SELECT * FROM actor where actor_id = ?')
        ->shouldReceive('getParameters')->andReturn(['-1'])
        ->shouldReceive('execute')->with('SELECT VERSION()', false)->andReturn([['VERSION()' => $results['version']]])
        ->shouldReceive('execute')->with("EXPLAIN FORMAT=JSON SELECT * FROM actor where actor_id = ?", true)->andReturn([['EXPLAIN' => $results['explain-json']]])
        ->shouldReceive('execute')->with("EXPLAIN FORMAT=TREE SELECT * FROM actor where actor_id = ?", true)->andReturn([['EXPLAIN' => $results['explain-tree']]])
        ->getMock();

    $metrics = (new Collector())->execute($query);

    expect($metrics)
        ->getQuery()->toBe($query)
        ->getVersion()->toBe($results['version'])
        ->getExplainJson()->toBe($results['explain-json'])
        ->getExplainTree()->toBe($results['explain-tree']);
})->with('results');

it('catches the error when FORMAT=TREE is not available', function (array $results): void {
    $query = Mockery::mock(QueryInterface::class)
        ->shouldReceive('getSql')->andReturn('SELECT * FROM actor where actor_id = ?')
        ->shouldReceive('getParameters')->andReturn(['-1'])
        ->shouldReceive('execute')->with('SELECT VERSION()', false)->andReturn([['VERSION()' => $results['version']]])
        ->shouldReceive('execute')->with("EXPLAIN FORMAT=JSON SELECT * FROM actor where actor_id = ?", true)->andReturn([['EXPLAIN' => $results['explain-json']]])
        ->shouldReceive('execute')->andThrow(RuntimeException::class)
        ->getMock();

    $metrics = (new Collector())->execute($query);

    expect($metrics)
        ->getQuery()->toBe($query)
        ->getVersion()->toBe($results['version'])
        ->getExplainJson()->toBe($results['explain-json'])
        ->getExplainTree()->toBeNull();
})->with('results');
