<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Queries;

trait Query
{
    protected string $sql;

    /**
     * @var array<array-key, mixed>
     */
    protected array $params;

    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array<array-key, float|int|string>
     */
    public function getParameters(): array
    {
        return array_map(function ($param) {
            if (is_float($param) || is_int($param)) {
                return $param;
            }

            return strval($param); // @phpstan-ignore argument.type
        }, $this->params);
    }
}
