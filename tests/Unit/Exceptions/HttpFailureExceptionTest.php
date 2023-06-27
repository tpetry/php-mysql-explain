<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use Http\Client\Exception\TransferException;
use Tpetry\PhpMysqlExplain\Exceptions\HttpFailureException;

it('returns constructor values', function (): void {
    $exception = new TransferException('Could not connect to host mysqlexplain.com.');

    expect(new HttpFailureException($exception))
        ->getMessage()->toBe('The EXPLAIN api request failed: Could not connect to host mysqlexplain.com.')
        ->getPrevious()->toBe($exception);
});
