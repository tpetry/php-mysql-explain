<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedDatabaseException;

it('returns constructor values', function (): void {
    expect(new UnsupportedDatabaseException('Only MySQL >=5.6 is supported.'))
        ->getMessage()->toBe('Only MySQL >=5.6 is supported.');
});
