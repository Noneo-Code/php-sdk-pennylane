<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use GuzzleHttp\RequestOptions;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

final class PennylaneTest extends TestCase
{
    public function testResolvesTheCompanyBaseUrl(): void
    {
        $pennylane = new Pennylane('a-token');

        self::assertSame('https://app.pennylane.com/api/external/v2', $pennylane->connector()->resolveBaseUrl());
    }

    public function testSetsTheBearerAuthorizationHeaderFromTheApiToken(): void
    {
        $pennylane = new Pennylane('company-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer company-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }

    /**
     * Un jeton d'accès OAuth est un Bearer comme un autre : le constructeur
     * unique couvre les deux provenances de jeton.
     */
    public function testSetsTheBearerAuthorizationHeaderFromAnOAuthAccessToken(): void
    {
        $pennylane = new Pennylane('oauth-access-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer oauth-access-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }

    public function testThrowsAPennylaneExceptionOnErrorResponses(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Not Found', 'status' => 404], 404),
        ]));

        $this->expectException(NotFoundException::class);

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries/999';
            }
        });
    }

    public function testAcceptsAnInjectedRateLimitStore(): void
    {
        MemoryStore::clear();

        $store = new MemoryStore();
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->useRateLimitStore($store);
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        self::assertNotEmpty($store->getStore());
    }

    public function testAppliesTheDefaultTimeoutsToOutgoingRequests(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(10.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(60.0, $config[RequestOptions::TIMEOUT] ?? null);
    }

    public function testAppliesTheTimeoutsGivenToTheConstructor(): void
    {
        $pennylane = new Pennylane(
            'a-token',
            requestTimeout: 120,
            connectTimeout: 3,
        );
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(3.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(120.0, $config[RequestOptions::TIMEOUT] ?? null);
    }

    public function testKeepsTheDefaultTimeoutWhenOnlyOneOfThemIsOverridden(): void
    {
        $pennylane = new Pennylane('a-token', requestTimeout: 5);

        self::assertSame(5.0, $pennylane->connector()->getRequestTimeout());
        self::assertSame(10.0, $pennylane->connector()->getConnectTimeout());
    }
}
