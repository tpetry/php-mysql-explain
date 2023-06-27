<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use DateTimeImmutable;
use Tpetry\PhpMysqlExplain\Exceptions\RateLimitExceededException;

it('returns constructor values', function (): void {
    expect(new RateLimitExceededException(1453675031))
        ->getMessage()->toBe('Too many attempts. Retry again at Sun, 24 Jan 2016 22:37:11 +0000.')
        ->getResetAt()->toEqual(new DateTimeImmutable('@1453675031'));
});
