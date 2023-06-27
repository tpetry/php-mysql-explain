<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Metrics;

use Mockery;
use Tpetry\PhpMysqlExplain\Metrics\Metrics;
use Tpetry\PhpMysqlExplain\Queries\QueryInterface;

it('returns constructor values', function (): void {
    $query = Mockery::mock(QueryInterface::class)
        ->shouldReceive('getSql')->andReturn('SELECT ?')
        ->shouldReceive('getParameters')->andReturn([1])
        ->getMock();

    $metrics = new Metrics(
        $query,
        '8.0.34',
        '{ "query_block": { "select_id": 1, "message": "No tables used" } }',
        '-> Rows fetched before execution  (cost=0..0 rows=1)',
    );

    expect($metrics)
        ->getQuery()->toBe($query)
        ->getVersion()->toBe('8.0.34')
        ->getExplainJson()->toBe('{ "query_block": { "select_id": 1, "message": "No tables used" } }')
        ->getExplainTree()->toBe('-> Rows fetched before execution  (cost=0..0 rows=1)');
});
