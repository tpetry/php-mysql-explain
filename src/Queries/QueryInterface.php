<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Queries;

interface QueryInterface
{
    /**
     * The name of the query driver (e.g. PDO, mysqli, Laravel, Doctrine, etc.).
     *
     * For custom drivers, naming your driver by the repository and version (e.g. tpetry/laravel-mysql-explain@v1) helps
     * in contacting you to provide feedback about issues etc.
     */
    public function name(): string;

    /**
     * The SQL query that should be executed.
     */
    public function getSql(): string;

    /**
     * The used parameters in string or numeric form.
     *
     * Some query drivers support using complex objects as parameters in queries. Those objects are internally
     * transformed into a value SQL understand. These same conversions need to be applied by this method to
     * correctly report the used parameters to the API endpoint.
     *
     * @return array<array-key, float|int|string>
     */
    public function getParameters(): array;

    /**
     * Execute the requested sql query with or without the parameters being applied.
     *
     * This method is used by the collector to execute the different queries to get the needed meta information for
     * showing the visual plan with all its details.
     *
     * @return array<int,array<string,mixed>>
     */
    public function execute(string $sql, bool $useParams): array;
}
