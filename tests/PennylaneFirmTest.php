<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use GuzzleHttp\RequestOptions;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

final class PennylaneFirmTest extends TestCase
{
    public function testResolvesTheFirmBaseUrl(): void
    {
        $firm = new PennylaneFirm('a-token');

        self::assertSame('https://app.pennylane.com/api/external/firm/v1', $firm->connector()->resolveBaseUrl());
    }

    public function testSetsTheBearerAuthorizationHeaderFromTheApiToken(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        });

        $mockClient->assertSent(function (Request $request, Response $response): bool {
            return 'Bearer firm-token' === $response->getPendingRequest()->headers()->get('Authorization');
        });
    }

    public function testThrowsAPennylaneExceptionOnErrorResponses(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $firm->connector()->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Not Found', 'status' => 404], 404),
        ]));

        $this->expectException(NotFoundException::class);

        $firm->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies/999';
            }
        });
    }

    public function testAcceptsAnInjectedRateLimitStore(): void
    {
        MemoryStore::clear();

        $store = new MemoryStore();
        $firm = new PennylaneFirm('firm-token');
        $firm->connector()->useRateLimitStore($store);
        $firm->connector()->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $firm->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        });

        self::assertNotEmpty($store->getStore());
    }

    public function testAppliesTheDefaultTimeoutsToOutgoingRequests(): void
    {
        $firm = new PennylaneFirm('firm-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        });

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(10.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(60.0, $config[RequestOptions::TIMEOUT] ?? null);
    }

    public function testAppliesTheTimeoutsGivenToTheConstructor(): void
    {
        $firm = new PennylaneFirm(
            'firm-token',
            requestTimeout: 90,
            connectTimeout: 4,
        );
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->connector()->withMockClient($mockClient);

        $firm->connector()->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        });

        $config = $mockClient->getLastResponse()?->getPendingRequest()->config()->all() ?? [];

        self::assertSame(4.0, $config[RequestOptions::CONNECT_TIMEOUT] ?? null);
        self::assertSame(90.0, $config[RequestOptions::TIMEOUT] ?? null);
    }
}
