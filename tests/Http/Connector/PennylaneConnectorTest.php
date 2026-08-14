<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http\Connector;

use Noneo\Sdk\Pennylane\Http\Connector\PennylaneConnector;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Stores\PsrStore;

final class PennylaneConnectorTest extends TestCase
{
    public function testResolvesTheCompanyBaseUrl(): void
    {
        $connector = new PennylaneConnector('company-token');

        self::assertSame('https://app.pennylane.com/api/external/v2', $connector->resolveBaseUrl());
    }

    public function testAuthenticatesWithTheBearerToken(): void
    {
        $connector = new PennylaneConnector('company-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $connector->withMockClient($mockClient);

        $connector->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/customers';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer company-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }

    public function testWrapsAPsr16CacheInAPsrStore(): void
    {
        $cache = $this->createStub(CacheInterface::class);

        $connector = new PennylaneConnector('t', rateLimitCache: $cache);

        self::assertInstanceOf(PsrStore::class, $connector->getRateLimitStore());
    }

    public function testDefaultsToAMemoryStoreWithoutCache(): void
    {
        $connector = new PennylaneConnector('t');

        self::assertInstanceOf(MemoryStore::class, $connector->getRateLimitStore());
    }
}
