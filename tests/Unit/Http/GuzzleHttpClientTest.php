<?php

declare(strict_types=1);

namespace Factorial\TwentyCrm\Tests\Unit\Http;

use Factorial\TwentyCrm\Auth\BearerTokenAuth;
use Factorial\TwentyCrm\Exception\ApiException;
use Factorial\TwentyCrm\Http\GuzzleHttpClient;
use Factorial\TwentyCrm\Tests\TestCase;
use Factorial\TwentyCrm\Http\BackoffRateLimiter;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;

/**
 * @covers \Factorial\TwentyCrm\Http\GuzzleHttpClient
 */
class GuzzleHttpClientTest extends TestCase
{
    public function testRetriesRateLimitedRequests(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $http->expects($this->exactly(3))->method('sendRequest')->willReturnOnConsecutiveCalls(
            new Response(429),
            new Response(429),
            new Response(200, [], '{"data":1}'),
        );

        $this->assertSame(['data' => 1], $this->client($http, 2)->request('GET', 'people'));
    }

    public function testGivesUpAfterMaxRetries(): void
    {
        $http = $this->createMock(ClientInterface::class);
        $http->expects($this->exactly(2))->method('sendRequest')
            ->willReturn(new Response(429, [], 'Rate limit exceeded'));

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(429);
        $this->client($http, 1)->request('GET', 'people');
    }

    private function client(ClientInterface $http, int $maxRetries): GuzzleHttpClient
    {
        $factory = new HttpFactory();
        return new GuzzleHttpClient(
            $http,
            $factory,
            $factory,
            new BearerTokenAuth('token'),
            'https://twenty.test/rest',
            rateLimiter: new BackoffRateLimiter($maxRetries, 0)
        );
    }
}
