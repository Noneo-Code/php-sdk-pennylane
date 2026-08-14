<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerAccounts;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerAccounts\UpdateLedgerAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateLedgerAccountTest extends TestCase
{
    public function testSendsAPutRequestWithTheProvidedFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 234])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateLedgerAccount(234, label: 'Capital', letterable: true));

        $mockClient->assertSent(function (UpdateLedgerAccount $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/ledger_accounts/234' === $sent->resolveEndpoint()
                && [
                    'label' => 'Capital',
                    'letterable' => true,
                ] === $sent->body()->all();
        });
    }

    public function testOmitsUnsetFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 234])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateLedgerAccount(234, letterable: false));

        $mockClient->assertSent(function (UpdateLedgerAccount $sent): bool {
            return ['letterable' => false] === $sent->body()->all();
        });
    }
}
