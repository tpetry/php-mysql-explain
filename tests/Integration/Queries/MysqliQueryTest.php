<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Integration\Queries;

use Dotenv\Dotenv;
use mysqli;
use PDO;
use Tpetry\PhpMysqlExplain\Queries\MysqliQuery;
use Tpetry\PhpMysqlExplain\Queries\PdoQuery;

beforeEach(function () {
    $dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../../..');
    $dotenv->safeLoad();
    $dotenv->required(['MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_DATABASE', 'MYSQL_USERNAME', 'MYSQL_PASSWORD'])->notEmpty();

    $this->connection = new mysqli(getenv('MYSQL_HOST'), getenv('MYSQL_USERNAME'), getenv('MYSQL_PASSWORD'), getenv('MYSQL_DATABASE'), (int) getenv('MYSQL_PORT'));
});


it('executes a query without parameters', function (): void {
    $query = new MysqliQuery($this->connection, 'SELECT 1 AS val');

    expect($query->execute('SELECT 1 AS val', false))
        ->toBe([['val' => 1]]);
});

it('executes a query with positional parameters (untyped)', function (): void {
    $query = new MysqliQuery($this->connection, 'SELECT ? AS val', [1]);

    expect($query->execute('SELECT ? AS val', true))
        ->toBe([['val' => '1']]);
});

it('executes a query with positional parameters (typed)', function (): void {
    $query = new MysqliQuery($this->connection, 'SELECT ? AS val', [1], 'i');

    expect($query->execute('SELECT ? AS val', true))
        ->toBe([['val' => 1]]);
});
