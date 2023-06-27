<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Queries;

use PDO;

class PdoQuery implements QueryInterface
{
    use Query;

    protected PDO $connection;

    /**
     * @var array<array-key, int>
     */
    protected array $types;

    /**
     * @param array<array-key, float|int|string> $params
     * @param array<array-key, int> $types
     */
    public function __construct(PDO $connection, string $sql, array $params = [], array $types = [])
    {
        $this->connection = $connection;
        $this->sql = $sql;
        $this->params = $params;
        $this->types = $types;
    }

    public function name(): string
    {
        return 'pdo';
    }

    public function execute(string $sql, bool $useParams): array
    {
        $errorMode = $this->connection->getAttribute(PDO::ATTR_ERRMODE);

        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $statement = $this->connection->prepare($sql);
            if ($useParams) {
                $isParamsList = array_is_list($this->params) && array_is_list($this->types);
                foreach ($this->params as $key => $value) {
                    $statement->bindValue($isParamsList ? $key + 1 : $key, $value, $this->types[$key] ?? PDO::PARAM_STR);
                }
            }
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } finally {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, $errorMode);
        }
    }
}
