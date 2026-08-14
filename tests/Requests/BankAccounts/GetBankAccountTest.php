<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BankAccounts;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\GetBankAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetBankAccountTest extends TestCase
{
    public function testSendsAGetRequestToTheBankAccountEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 42,
            'name' => 'Main account',
            'currency' => 'EUR',
            'balance' => '100.15',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 42],
            'journal' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/journals/7'],
            'ledger_account' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/8'],
        ])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetBankAccount(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/bank_accounts/42' === $sent->resolveEndpoint();
        });
    }
}
