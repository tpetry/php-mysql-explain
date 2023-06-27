<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Unit\Queries;

use Mockery;
use mysqli;
use mysqli_result;
use mysqli_stmt;
use Tpetry\PhpMysqlExplain\Queries\MysqliQuery;

it('identified as pdo driver', function (): void {
    $query = new MysqliQuery(Mockery::mock(mysqli::class), 'SELECT * FROM example');

    expect($query)
        ->name()->toBe('mysqli');
});

it('returns constructor values', function (): void {
    $query = new MysqliQuery(Mockery::mock(mysqli::class), 'SELECT * FROM example WHERE id = ?', ['1']);

    expect($query)
        ->getSql()->toBe('SELECT * FROM example WHERE id = ?')
        ->getParameters()->toBe(['1']);
});

it('executes requested query with no parameters', function (array $parameters): void {
    $result = Mockery::mock(mysqli_result::class)
        ->shouldReceive('fetch_all')->once()->with(MYSQLI_ASSOC)->andReturn([['VERSION' => '8.0.34']])
        ->getMock();
    $statement = Mockery::mock(mysqli_stmt::class)
        ->shouldReceive('execute')->once()
        ->shouldReceive('get_result')->once()->andReturn($result)
        ->getMock();
    $connection = Mockery::mock(mysqli::class)
        ->shouldReceive('prepare')->once()->with('SELECT VERSION()')->andReturn($statement)
        ->getMock();
    $query = new MysqliQuery($connection, 'SELECT * FROM actor', $parameters);

    $result = $query->execute('SELECT VERSION()', false);

    expect($result)
        ->toBe([['VERSION' => '8.0.34']]);
})->with([[[]], [['1']]]);

it('executes requested query with positional parameters (untyped)', function (): void {
    $result = Mockery::mock(mysqli_result::class)
        ->shouldReceive('fetch_all')->once()->with(MYSQLI_ASSOC)->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $statement = Mockery::mock(mysqli_stmt::class)
        ->shouldReceive('bind_param')->once()->with('s', [-1])
        ->shouldReceive('execute')->once()
        ->shouldReceive('get_result')->once()->andReturn($result)
        ->getMock();
    $connection = Mockery::mock(mysqli::class)
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?')->andReturn($statement)
        ->getMock();
    $query = new MysqliQuery($connection, 'SELECT * FROM actor WHERE id = ?', [-1]);

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});

it('executes requested query with positional parameters (typed)', function (): void {
    $result = Mockery::mock(mysqli_result::class)
        ->shouldReceive('fetch_all')->once()->with(MYSQLI_ASSOC)->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $statement = Mockery::mock(mysqli_stmt::class)
        ->shouldReceive('bind_param')->once()->with('i', [-1])
        ->shouldReceive('execute')->once()
        ->shouldReceive('get_result')->once()->andReturn($result)
        ->getMock();
    $connection = Mockery::mock(mysqli::class)
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?')->andReturn($statement)
        ->getMock();
    $query = new MysqliQuery($connection, 'SELECT * FROM actor WHERE id = ?', [-1], 'i');

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});
