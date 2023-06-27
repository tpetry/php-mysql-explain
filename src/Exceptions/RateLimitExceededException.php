<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Exceptions;

use DateTimeImmutable;
use DateTimeInterface;

class RateLimitExceededException extends ExplainException
{
    private int $resetAt;

    public function __construct(int $resetAt)
    {
        $resetAtStr = (new DateTimeImmutable("@{$resetAt}"))->format('r');

        parent::__construct("Too many attempts. Retry again at $resetAtStr.");
        $this->resetAt = $resetAt;
    }

    public function getResetAt(): DateTimeInterface
    {
        return new DateTimeImmutable("@{$this->resetAt}");
    }
}
