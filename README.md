# MySQL Visual Explains with PHP

![License][icon-license]
![PHP][icon-php]
[![Latest Version on Packagist][icon-version]][href-version]
[![GitHub Unit Tests Action Status][icon-tests]][href-tests]
[![GitHub Static Analysis Action Status][icon-staticanalysis]][href-staticanalysis]

MySQL Query optimization with the EXPLAIN command is unnecessarily complicated:
The output contains a lot of cryptic information that is incomprehensible or entirely misleading.

This PHP package collects many query metrics that will be sent to [mysqlexplain.com](https://mysqlexplain.com) and transformed to be much easier to understand.

## Installation

You can install the package via composer:

```bash
composer require tpetry/php-mysql-explain
```

## Usage

### 1. Query Definition

The query you want to analyze must first be defined with all the parameters used and its database connection.
Included are implementations for PHP's mysqli and PDO drivers.
However, you can also build framework-specific ones by implementing the `QueryInterface`.

> [!NOTE]  
> The minimum required PHP version is 7.4 but the examples use the named arguments syntax of PHP 8 for easier reading.

#### mysqli Driver

In its most simple form, you only provide the mysqli connection and the query to execute:

```php
use Tpetry\PhpMysqlExplain\Queries\MysqliQuery;

$mysqli = new mysqli('127.0.0.1', 'root', 'root', 'github');

$query = new MysqliQuery(
  connection: $mysqli,
  sql: 'SELECT * FROM issues',
);
```

You can also provide variables that are bound to the prepared statement:

```php
$query = new MysqliQuery(
  connection: $mysqli,
  sql: 'SELECT * FROM issues WHERE type = ? AND num > ?',
  params: ['finished', 85],
);
```

Please be aware that by default all passed variables are interpreted as strings.
However, you can pass a string as last parameter following the characteristics of the `$types` parameter from mysqli's [bind_param](https://www.php.net/manual/en/mysqli-stmt.bind-param.php) function:

```php
$query = new MysqliQuery(
  connection: $mysqli,
  sql: 'SELECT * FROM issues WHERE type = ? AND num > ?',
  params: ['finished', 85],
  types: 'si',
);
```

#### PDO Driver


```php
use Tpetry\PhpMysqlExplain\Queries\PdoQuery;

$pdo = new PDO('mysql:host=127.0.0.1;dbname=github', 'root', 'root');

$query = new PdoQuery(
  connection: $pdo,
  sql: 'SELECT * FROM issues',
);
```


You can also provide variables that are bound to the prepared statement with positional or named parameters:

```php
$query = new PdoQuery(
  connection: $pdo,
  sql: 'SELECT * FROM issues WHERE type = ? AND num > ?',
  params: ['finished', 85],
);

$query = new PdoQuery(
  connection: $pdo,
  sql: 'SELECT * FROM issues WHERE type = :type AND num > :num',
  params: ['type' => 'finished', 'num' => 85],
);
```

Please be aware that by default all passed variables are interpreted as strings.
However, you can pass an array as last parameter with [PDO::PARAM_*](https://www.php.net/manual/en/pdo.constants.php) types:

```php
$query = new PdoQuery(
  connection: $pdo,
  sql: 'SELECT * FROM issues WHERE type = ? AND num > ?',
  params: ['finished', 85],
  types: [PDO::PARAM_STR, PDO::PARAM_INT],
);

$query = new PdoQuery(
  connection: $pdo,
  sql: 'SELECT * FROM issues WHERE type = :type AND num > :num',
  params: ['type' => 'finished', 'num' => 85],
  types: ['type' => PDO::PARAM_STR, 'num' => PDO::PARAM_INT],
);
```

### 2. Metric Collection

Now, the query profiling information must be collected for the previously configured query:

```php
use Tpetry\PhpMysqlExplain\Metrics\Collector;

$metrics = (new Collector())->execute($query);
```

### 3. API Call

Finally, the `$metrics` object containing all the information must be sent to the [mysqlexplain.com API](https://api.mysqlexplain.com):

```php
use GuzzleHttp\Client as GuzzleClient;
use Tpetry\PhpMysqlExplain\Api\Client;

$client = new Client(new GuzzleClient());
$response = $client->submit($metrics);

var_dump($response->getUrl());
```

> [!IMPORTANT]
> This package has no dependency on any specific HTTP client library to avoid conflicts with actively used versions within your project.
> Therefore, you must pass an object of any http library implementing `psr/http-client`.
> In this example, the popular [Guzzle](https://docs.guzzlephp.org/en/stable/index.html) HTTP library (`composer require guzzlehttp/guzzle`) was used - which you probably have already installed.
> But you can also choose from [many other implementations](https://packagist.org/providers/psr/http-client-implementation).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [tpetry](https://github.com/tpetry)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[href-staticanalysis]: https://github.com/tpetry/php-mysql-explain/actions/workflows/phpstan.yml
[href-tests]: https://github.com/tpetry/php-mysql-explain/actions/workflows/phpunit.yml
[href-version]: https://packagist.org/packages/tpetry/php-mysql-explain
[icon-license]: https://img.shields.io/github/license/tpetry/php-mysql-explain?color=blue&label=License
[icon-php]: https://img.shields.io/packagist/php-v/tpetry/php-mysql-explain?color=blue&label=PHP
[icon-staticanalysis]: https://img.shields.io/github/actions/workflow/status/tpetry/php-mysql-explain/static-analysis.yml?label=Static%20Analysis
[icon-tests]: https://img.shields.io/github/actions/workflow/status/tpetry/php-mysql-explain/unit-tests.yml?label=Tests
[icon-version]: https://img.shields.io/packagist/v/tpetry/php-mysql-explain.svg?label=Packagist
