<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use Tpetry\PhpMysqlExplain\Exceptions\ValidationFailedException;

it('returns constructor values', function (): void {
    $errors = [
        ['attribute' => 'query', 'message' => 'This valus is required.'],
        ['attribute' => 'version', 'message' => 'This valus is invalid.'],
    ];

    expect(new ValidationFailedException($errors))
        ->getMessage()->toBe("Validation for the EXPLAIN information failed:\n* query: This valus is required.\n* version: This valus is invalid.")
        ->getErrors()->toBe($errors);
});
