<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedQueryException;

it('returns constructor values', function (): void {
    expect(new UnsupportedQueryException('Only SELECT queries are supported.'))
        ->getMessage()->toBe('Only SELECT queries are supported.');
});
