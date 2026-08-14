<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerAccounts;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\GetLedgerAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetLedgerAccountTest extends TestCase
{
    public function testSendsAGetRequestToTheLedgerAccountEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 123])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetLedgerAccount(123));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/ledger_accounts/123' === $sent->resolveEndpoint();
        });
    }
}
