<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Tests\Api;

use DateTimeImmutable;
use Http\Client\Exception\TransferException;
use Http\Mock\Client as HttpMock;
use Nyholm\Psr7\Response;
use Tpetry\PhpMysqlExplain\Api\Client;
use Tpetry\PhpMysqlExplain\Exceptions\HttpFailureException;
use Tpetry\PhpMysqlExplain\Exceptions\RateLimitExceededException;
use Tpetry\PhpMysqlExplain\Exceptions\UnknownResponseException;
use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedDatabaseException;
use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedQueryException;
use Tpetry\PhpMysqlExplain\Exceptions\ValidationFailedException;
use Tpetry\PhpMysqlExplain\Metrics\Metrics;
use Tpetry\PhpMysqlExplain\Queries\QueryInterface;

dataset('metrics', [
    new Metrics(
        new class() implements QueryInterface {
            public function name(): string { return 'test'; }
            public function getSql(): string { return 'SELECT * FROM example WHERE id = ?'; }
            public function getParameters(): array { return [1]; }
            public function execute(string $sql, bool $useParams): array { return []; }
        },
        '8.0.0',
        '{ "query_block": {} }',
        '-> Executing Query  (cost=0.82 rows=5.48)',
    ),
]);

it('sends data to tha api and returns the result', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(200, [], '{"url":"https://mysqlexplain.com/explain/01j2ej50ytfra85rz2ax9w6zyw"}'));
    $client = new Client($http);

    $result = $client->submit($metrics, 'Testcase');

    expect($result)
        ->getUrl()->toBe('https://mysqlexplain.com/explain/01j2ej50ytfra85rz2ax9w6zyw');
    expect($http->getLastRequest())
        ->getMethod()->toBe('POST')
        ->getUri()->__toString()->toBe('https://api.mysqlexplain.com/v2/explains')
        ->getHeaders()->toMatchArray([
            'Accept' => ['application/json'],
            'Content-Type' => ['application/json'],
            'User-Agent' => ['tpetry/php-mysql-explain@1.0.0'],
            'X-Driver' => ['test'],
        ])
        ->getBody()->getContents()->json()->toBe([
            'query' => $metrics->getQuery()->getSql(),
            'bindings' => $metrics->getQuery()->getParameters(),
            'version' => $metrics->getVersion(),
            'explain_json' => $metrics->getExplainJson(),
            'explain_tree' => $metrics->getExplainTree(),
        ]);
})->with('metrics');

it('throws a UnsupportedDatabaseException when the database is not supported', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(400, [], '{"error":"unsupported_database", "message":"Only MySQL >=5.6 is supported."}'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(UnsupportedDatabaseException $e) => expect($e)
                ->getMessage()->toBe('Only MySQL >=5.6 is supported.'),
        );
})->with('metrics');

it('throws a UnsupportedQueryException when the sql query is not supported', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(400, [], '{"error":"unsupported_query", "message":"Only SELECT queries are supported."}'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(UnsupportedQueryException $e) => expect($e)
                ->getMessage()->toBe('Only SELECT queries are supported.'),
        );
})->with('metrics');

it('throws a ValidationFailedException when the request parameters fail validation', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(422, [], '{"errors":[{"attribute":"query","message":"The value is required."}]}'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(ValidationFailedException $e) => expect($e)
                ->getErrors()->toBe([['attribute' => 'query', 'message' => "The value is required."]]),
        );
})->with('metrics');

it('throws a RateLimitExceededException when the rate limit has been reached', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(429, ['X-Ratelimit-Limit' => '999', 'X-Ratelimit-Remaining' => 0, 'X-Ratelimit-Reset' => 1880442207]));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(RateLimitExceededException $e) => expect($e)
                ->getResetAt()->toEqual(new DateTimeImmutable('@1880442207')),
        );
})->with('metrics');

it('throws a UnknownResponseException for unknown 400 errors', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(400, [], '{"error":"unknown_code", "message":"This is for future-proofing."}'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(UnknownResponseException $e) => expect($e)
                ->getStatusCode()->toBe(400)
                ->getStatusMessage()->toBe('Bad Request')
                ->getBody()->toBe('{"error":"unknown_code", "message":"This is for future-proofing."}'),
        );
})->with('metrics');

it('throws a UnknownResponseException for undocumented responses', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addResponse(new Response(500, [], 'Whoops, looks like something went wrong.'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(UnknownResponseException $e) => expect($e)
                ->getStatusCode()->toBe(500)
                ->getStatusMessage()->toBe('Internal Server Error')
                ->getBody()->toBe('Whoops, looks like something went wrong.'),
        );
})->with('metrics');

it('throws a HttpFailureException when sending the API call failed', function (Metrics $metrics): void {
    $http = new HttpMock();
    $http->addException($exception = new TransferException('Request Failed.'));
    $client = new Client($http);

    expect(fn() => $client->submit($metrics))
        ->toThrow(
            fn(HttpFailureException $e) => expect($e)
                ->getMessage()->toBe('The EXPLAIN api request failed: Request Failed.')
                ->getPrevious()->toBe($exception),
        );
})->with('metrics');
