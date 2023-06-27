<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Unit\Queries;

use Mockery;
use PDO;
use PDOStatement;
use Tpetry\PhpMysqlExplain\Queries\PdoQuery;

it('identified as pdo driver', function (): void {
    $query = new PdoQuery(Mockery::mock(PDO::class), 'SELECT * FROM example');

    expect($query)
        ->name()->toBe('pdo');
});

it('returns constructor values', function (): void {
    $query = new PdoQuery(Mockery::mock(PDO::class), 'SELECT * FROM example WHERE id = ?', ['1']);

    expect($query)
        ->getSql()->toBe('SELECT * FROM example WHERE id = ?')
        ->getParameters()->toBe(['1']);
});

it('executes requested query with no parameters', function (array $parameters): void {
    $statement = Mockery::mock(PDOStatement::class)
        ->shouldReceive('execute')->once()
        ->shouldReceive('fetchAll')->once()->andReturn([['VERSION' => '8.0.34']])
        ->getMock();
    $connection = Mockery::mock(PDO::class)
        ->shouldReceive('getAttribute')
        ->shouldReceive('setAttribute')
        ->shouldReceive('prepare')->once()->with('SELECT VERSION()')->andReturn($statement)
        ->getMock();
    $query = new PdoQuery($connection, 'SELECT * FROM actor', $parameters);

    $result = $query->execute('SELECT VERSION()', false);

    expect($result)
        ->toBe([['VERSION' => '8.0.34']]);
})->with([[[]], [['1']]]);

it('executes requested query with positional parameters (untyped)', function (): void {
    $statement = Mockery::mock(PDOStatement::class)
        ->shouldReceive('bindValue')->once()->with(1, -1, PDO::PARAM_STR)
        ->shouldReceive('execute')->once()
        ->shouldReceive('fetchAll')->once()->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $connection = Mockery::mock(PDO::class)
        ->shouldReceive('getAttribute')
        ->shouldReceive('setAttribute')
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?')->andReturn($statement)
        ->getMock();
    $query = new PdoQuery($connection, 'SELECT * FROM actor WHERE id = ?', [-1]);

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});

it('executes requested query with positional parameters (typed)', function (): void {
    $statement = Mockery::mock(PDOStatement::class)
        ->shouldReceive('bindValue')->once()->with(1, -1, PDO::PARAM_INT)
        ->shouldReceive('execute')->once()
        ->shouldReceive('fetchAll')->once()->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $connection = Mockery::mock(PDO::class)
        ->shouldReceive('getAttribute')
        ->shouldReceive('setAttribute')
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?')->andReturn($statement)
        ->getMock();
    $query = new PdoQuery($connection, 'SELECT * FROM actor WHERE id = ?', [-1], [PDO::PARAM_INT]);

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = ?', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});

it('executes requested query with names parameters (untyped)', function (): void {
    $statement = Mockery::mock(PDOStatement::class)
        ->shouldReceive('bindValue')->once()->with('id', -1, PDO::PARAM_STR)
        ->shouldReceive('execute')->once()
        ->shouldReceive('fetchAll')->once()->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $connection = Mockery::mock(PDO::class)
        ->shouldReceive('getAttribute')
        ->shouldReceive('setAttribute')
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = :id')->andReturn($statement)
        ->getMock();
    $query = new PdoQuery($connection, 'SELECT * FROM actor WHERE id = :id', ['id' => -1]);

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = :id', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});

it('executes requested query with named parameters (typed)', function (): void {
    $statement = Mockery::mock(PDOStatement::class)
        ->shouldReceive('setAttribute')
        ->shouldReceive('bindValue')->once()->with('id', -1, PDO::PARAM_INT)
        ->shouldReceive('execute')->once()
        ->shouldReceive('fetchAll')->once()->andReturn([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']])
        ->getMock();
    $connection = Mockery::mock(PDO::class)
        ->shouldReceive('getAttribute')
        ->shouldReceive('setAttribute')
        ->shouldReceive('prepare')->once()->with('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = :id')->andReturn($statement)
        ->getMock();
    $query = new PdoQuery($connection, 'SELECT * FROM actor WHERE id = :id', ['id' => -1], ['id' => PDO::PARAM_INT]);

    $result = $query->execute('EXPLAIN FORMAT=TREE SELECT * FROM actor WHERE id = :id', true);

    expect($result)
        ->toBe([['EXPLAIN' => '-> Zero rows (Impossible WHERE)  (cost=0..0 rows=0)']]);
});
