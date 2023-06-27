<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Exceptions;

use Psr\Http\Client\ClientExceptionInterface;

class HttpFailureException extends ExplainException
{
    public function __construct(ClientExceptionInterface $exception)
    {
        parent::__construct("The EXPLAIN api request failed: {$exception->getMessage()}", 0, $exception);
    }
}
