<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Exceptions;

use Tpetry\PhpMysqlExplain\Exceptions\UnknownResponseException;

it('returns constructor values', function (): void {
    expect(new UnknownResponseException(500, 'Internal Server Error', 'Whoops, looks like something went wrong.'))
        ->getMessage()->toBe('Unknown HTTP response. 500 Internal Server Error (Whoops, looks like something went wrong.)')
        ->getStatusCode()->toBe(500)
        ->getStatusMessage()->toBe('Internal Server Error')
        ->getBody()->toBe('Whoops, looks like something went wrong.');
});
