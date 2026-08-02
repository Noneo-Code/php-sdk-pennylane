<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use Noneo\Sdk\Pennylane\CompanyScope;
use Noneo\Sdk\Pennylane\Exception\NotFoundException;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Limit;
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

    /**
     * Le préfixe vient de `resolveBaseUrl()`, pas d'une mutation de la requête.
     * Une instance de `Request` réutilisée ne doit donc jamais accumuler le
     * scope : `/companies/1234/companies/1234/…` a déjà été livré une fois.
     */
    public function testDoesNotPrefixTwiceWhenTheSameRequestInstanceIsSentAgain(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([
            MockResponse::make(['id' => 1]),
            MockResponse::make(['id' => 2]),
        ]);
        $firm->withMockClient($mockClient);

        $scope = $firm->company(1234);
        $request = new LedgerEntriesProbe();

        $scope->send($request);
        $scope->send($request);

        self::assertSame(
            'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries',
            $mockClient->getLastResponse()?->getPendingRequest()->getUrl(),
        );
    }

    public function testScopesEachCompanyIndependentlyForASharedRequestInstance(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([
            MockResponse::make(['id' => 1]),
            MockResponse::make(['id' => 2]),
        ]);
        $firm->withMockClient($mockClient);

        $request = new LedgerEntriesProbe();

        $firm->company(111)->send($request);
        $first = $mockClient->getLastResponse()?->getPendingRequest()->getUrl();

        $firm->company(222)->send($request);
        $second = $mockClient->getLastResponse()?->getPendingRequest()->getUrl();

        self::assertSame('https://app.pennylane.com/api/external/firm/v1/companies/111/ledger_entries', $first);
        self::assertSame('https://app.pennylane.com/api/external/firm/v1/companies/222/ledger_entries', $second);
    }

    /**
     * Le quota Pennylane se décompte par jeton. Le cabinet et ses scopes
     * entreprise partagent le même jeton : ils doivent donc partager le même
     * compteur, sans quoi un cabinet suivant trois entreprises s'autoriserait
     * quatre fois le débit réellement accordé.
     */
    public function testSharesTheLimiterKeyWithTheFirmConnector(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));

        self::assertSame($this->limiterNames($firm), $this->limiterNames($firm->company(1234)));
    }

    public function testSharesTheLimiterKeyBetweenTwoCompanyScopes(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));

        self::assertSame(
            $this->limiterNames($firm->company(111)),
            $this->limiterNames($firm->company(222)),
        );
    }

    /**
     * @return list<string>
     */
    private function limiterNames(PennylaneFirm|CompanyScope $connector): array
    {
        return array_values(array_map(
            static fn (Limit $limit): string => $limit->getName(),
            $connector->getLimits(),
        ));
    }
}

final class LedgerEntriesProbe extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/ledger_entries';
    }
}
