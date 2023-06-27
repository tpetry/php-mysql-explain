<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Exceptions;

class UnknownResponseException extends ExplainException
{
    private string $body;

    private int $statusCode;

    private string $statusMessage;

    public function __construct(int $statusCode, string $statusMessage, string $body)
    {
        parent::__construct("Unknown HTTP response. {$statusCode} {$statusMessage} ({$body})");
        $this->statusCode = $statusCode;
        $this->statusMessage = $statusMessage;
        $this->body = $body;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }
}
