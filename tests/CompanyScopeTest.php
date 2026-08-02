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

final class CompanyScopeTest extends TestCase
{
    public function testResolvesTheCompanyScopedBaseUrl(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));

        self::assertSame(
            'https://app.pennylane.com/api/external/firm/v1/companies/1234',
            $firm->company(1234)->resolveBaseUrl(),
        );
    }

    public function testPrefixesTheRequestUrlWithTheCompanyId(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/ledger_entries';
            }
        });

        $mockClient->assertSent(function (Request $request): bool {
            return '/ledger_entries' === $request->resolveEndpoint();
        });

        $mockClient->assertSentCount(1);
        self::assertSame(
            'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries',
            $mockClient->getLastResponse()?->getPendingRequest()->getUrl(),
        );
    }

    public function testInheritsTheAuthenticatorFromTheFirmConnector(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1])]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/journals';
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

        $firm->company(1234)->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/journals/999';
            }
        });
    }

    public function testSharesTheRateLimitStoreWithTheFirmConnector(): void
    {
        MemoryStore::clear();

        $store = new MemoryStore();
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'), $store);
        $firm->withMockClient(new MockClient([MockResponse::make(['id' => 1])]));

        $firm->company(1234)->send(new class extends Request {
            protected Method $method = Method::GET;

            public function resolveEndpoint(): string
            {
                return '/journals';
            }
        });

        self::assertNotEmpty($store->getStore());
    }
}
