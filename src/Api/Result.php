<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Api;

class Result
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
