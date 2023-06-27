<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Metrics;

use RuntimeException;
use Throwable;
use Tpetry\PhpMysqlExplain\Queries\QueryInterface;

class Collector
{
    public function execute(QueryInterface $query): Metrics
    {
        $version = $this->scalar($query->execute('SELECT VERSION()', false));
        $explainJson = $this->scalar($query->execute("EXPLAIN FORMAT=JSON {$query->getSql()}", true));
        $explainTree = $this->rescue(fn() => $this->scalar($query->execute("EXPLAIN FORMAT=TREE {$query->getSql()}", true)));

        return new Metrics(
            $query,
            $version,
            $explainJson,
            $explainTree,
        );
    }

    /**
     * @template T
     *
     * @param callable(): T $fn
     * @return ?T
     */
    private function rescue(callable $fn)
    {
        try {
            return $fn();
        } catch (Throwable $t) {
            return null;
        }
    }

    /**
     * @param array<int, non-empty-array<string, float|int|string>> $rows
     */
    protected function scalar(array $rows): string
    {
        if (count($rows) !== 1) {
            throw new RuntimeException('Result has more than one row.');
        }

        $row = $rows[0];
        if (count($row) !== 1) {
            throw new RuntimeException('Result has more than one column.');
        }

        return strval($row[array_key_first($row)]);
    }
}
