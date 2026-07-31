<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests;

use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class CompanyScopeTest extends TestCase
{
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
}
