<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Exceptions;

class ValidationFailedException extends ExplainException
{
    /**
     * @var array<int, array{attribute: string, message: string}>
     */
    private array $errors;

    /**
     * @param array<int, array{attribute: string, message: string}> $errors
     */
    public function __construct(array $errors)
    {
        $errorsStr = implode("\n", array_map(fn(array $error) => "* {$error['attribute']}: {$error['message']}", $errors));

        parent::__construct("Validation for the EXPLAIN information failed:\n{$errorsStr}");
        $this->errors = $errors;
    }

    /**
     * @return array<int, array{attribute: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
