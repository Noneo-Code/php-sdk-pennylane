<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\BankAccounts;

use Noneo\Sdk\Pennylane\Dto\BankAccount\AccountType;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\PostBankAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostBankAccountTest extends TestCase
{
    public function testSendsAPostRequestToTheBankAccountsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 42,
            'name' => 'Main Account',
            'currency' => 'EUR',
            'balance' => '0.0',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 25],
            'journal' => null,
            'ledger_account' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/8'],
        ], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostBankAccount(
            name: 'Main Account',
            bankEstablishmentId: 25,
            iban: 'FR1420041010050500013M02606',
            bic: 'BNPAFRPPXXX',
            currency: Currency::Eur,
            accountType: AccountType::Checking,
        ));

        $mockClient->assertSent(function (PostBankAccount $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/bank_accounts' === $sent->resolveEndpoint()
                && [
                    'bank_establishment_id' => 25,
                    'name' => 'Main Account',
                    'iban' => 'FR1420041010050500013M02606',
                    'bic' => 'BNPAFRPPXXX',
                    'currency' => 'EUR',
                    'account_type' => 'checking',
                ] === $sent->body()->all();
        });
    }

    public function testOmitsOptionalFieldsFromTheBody(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([
            'id' => 42,
            'name' => 'Main Account',
            'currency' => 'EUR',
            'balance' => '0.0',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 25],
            'journal' => null,
            'ledger_account' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/8'],
        ], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostBankAccount(name: 'Main Account'));

        $mockClient->assertSent(function (PostBankAccount $sent): bool {
            return ['name' => 'Main Account'] === $sent->body()->all();
        });
    }
}
