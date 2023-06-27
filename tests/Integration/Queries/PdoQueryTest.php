<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Integration\Queries;

use Dotenv\Dotenv;
use PDO;
use Tpetry\PhpMysqlExplain\Queries\PdoQuery;

beforeEach(function () {
    $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../..');
    $dotenv->safeLoad();
    $dotenv->required(['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DATABASE', 'MYSQL_USERNAME', 'MYSQL_PASSWORD'])->notEmpty();

    $this->connection = new PDO(
        sprintf("mysql:host=%s;port=%s;dbname=%s", getenv('MYSQL_HOST'), getenv('MYSQL_PORT'), getenv('MYSQL_DATABASE')),
        getenv('MYSQL_USERNAME'),
        getenv('MYSQL_PASSWORD'),
    );
});

it('executes a query without parameters', function (): void {
    $query = new PdoQuery($this->connection, 'SELECT 1 AS val');

    expect($query->execute('SELECT 1 AS val', true))
        ->toEqual([['val' => 1]]);
});

it('executes a query with positional parameters (untyped)', function (): void {
    $query = new PdoQuery($this->connection, 'SELECT ? AS val', [1]);

    expect($query->execute('SELECT ? AS val', true))
        ->toEqual([['val' => '1']]);
});

it('executes a query with positional parameters (typed)', function (): void {
    $query = new PdoQuery($this->connection, 'SELECT ? AS val', [1], [PDO::PARAM_INT]);

    expect($query->execute('SELECT ? AS val', true))
        ->toEqual([['val' => 1]]);
});

it('executes a query with named parameters (untyped)', function (): void {
    $query = new PdoQuery($this->connection, 'SELECT :num', ['num' => 1]);

    expect($query->execute('SELECT :num AS val', true))
        ->toEqual([['val' => '1']]);
});

it('executes a query with named parameters (typed)', function (): void {
    $query = new PdoQuery($this->connection, 'SELECT :num AS val', ['num' => 1], ['num' => PDO::PARAM_INT]);

    expect($query->execute('SELECT :num AS val', true))
        ->toEqual([['val' => 1]]);
});
