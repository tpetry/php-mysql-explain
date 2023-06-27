<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Queries;

use mysqli;
use mysqli_driver;

class MysqliQuery implements QueryInterface
{
    use Query;

    protected mysqli $connection;

    protected ?string $types;

    /**
     * @param array<array-key, float|int|string> $params
     */
    public function __construct(mysqli $connection, string $sql, array $params = [], ?string $types = null)
    {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->params = $params;
        $this->types = $types;
    }

    public function name(): string
    {
        return 'mysqli';
    }

    public function execute(string $sql, bool $useParams): array
    {
        $mysqliDriver = new mysqli_driver();
        $reportMode = $mysqliDriver->report_mode;

        try {
            $mysqliDriver->report_mode |= MYSQLI_REPORT_ALL;

            $statement = $this->connection->prepare($sql);
            if ($useParams && count($this->params) > 0) {
                $statement->bind_param($this->types ?? str_repeat('s', count($this->params)), ...$this->params);
            }
            $statement->execute();

            return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        } finally {
            $mysqliDriver->report_mode = $reportMode;
        }
    }
}
