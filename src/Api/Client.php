<?php

declare(strict_types=1);

namespace Tpetry\PhpMysqlExplain\Api;

use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Tpetry\PhpMysqlExplain\Exceptions\HttpFailureException;
use Tpetry\PhpMysqlExplain\Exceptions\RateLimitExceededException;
use Tpetry\PhpMysqlExplain\Exceptions\UnknownResponseException;
use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedDatabaseException;
use Tpetry\PhpMysqlExplain\Exceptions\UnsupportedQueryException;
use Tpetry\PhpMysqlExplain\Exceptions\ValidationFailedException;
use Tpetry\PhpMysqlExplain\Metrics\Metrics;

class Client
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function submit(Metrics $metrics): Result
    {
        try {
            $request = new Request(
                'POST',
                'https://api.mysqlexplain.com/v2/explains',
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => "tpetry/php-mysql-explain@1.0.0",
                    'X-Driver' => $metrics->getQuery()->name(),
                ],
                json_encode([
                    'query' => $metrics->getQuery()->getSql(),
                    'bindings' => $metrics->getQuery()->getParameters(),
                    'version' => $metrics->getVersion(),
                    'explain_json' => $metrics->getExplainJson(),
                    'explain_tree' => $metrics->getExplainTree(),
                ], JSON_THROW_ON_ERROR),
            );
            $response = $this->client->sendRequest($request);

            switch ($response->getStatusCode()) {
                case 200:
                    /** @var array{url: string} $json */
                    $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                    return new Result($json['url']);
                case 400:
                    /** @var array{error: string, message: string} $json */
                    $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                    if ($json['error'] === 'unsupported_database') {
                        throw new UnsupportedDatabaseException($json['message']);
                    } elseif ($json['error'] === 'unsupported_query') {
                        throw new UnsupportedQueryException($json['message']);
                    }

                    break;
                case 422:
                    /** @var array{errors: array<int, array{attribute: string, message: string}>} $json */
                    $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                    throw new ValidationFailedException($json['errors']);
                case 429:
                    /** @var array{numeric-string} $reset */
                    $reset = $response->getHeader('X-RateLimit-Reset');

                    throw new RateLimitExceededException((int) $reset[0]);
            }

            $body = $response->getBody();
            $body->rewind();

            throw new UnknownResponseException($response->getStatusCode(), $response->getReasonPhrase(), $body->getContents());
        } catch (ClientExceptionInterface $e) {
            throw new HttpFailureException($e);
        }
    }
}
