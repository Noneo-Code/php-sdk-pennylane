<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

final class PennylaneFirmTest extends TestCase
{
    public function testResolvesTheFirmBaseUrl(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('a-token'));

        self::assertSame('https://app.pennylane.com/api/external/firm/v1', $firm->resolveBaseUrl());
    }

    public function testSetsTheBearerAuthorizationHeaderFromTheGivenAuthenticator(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->withMockClient($mockClient);

        $firm->send(new class extends Request {
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
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $firm->withMockClient(new MockClient([
            MockResponse::make(['error' => 'Not Found', 'status' => 404], 404),
        ]));

        $this->expectException(NotFoundException::class);

        $firm->send(new class extends Request {
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
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'), $store);
        $firm->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $firm->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/companies';
            }
        });

        self::assertNotEmpty($store->getStore());
    }
}
