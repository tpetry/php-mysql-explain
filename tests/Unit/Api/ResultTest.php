<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Api;

use Tpetry\PhpMysqlExplain\Api\Result;

it('returns constructor values', function (): void {
    $result = new Result('https://mysqlexplain.com/explain/01j2ef1bj7efr97m5v140rnxyz');

    expect($result)
        ->getUrl()->toBe('https://mysqlexplain.com/explain/01j2ef1bj7efr97m5v140rnxyz');
});
