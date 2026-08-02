<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\BankAccount\AccountType;
use Noneo\Sdk\Pennylane\Dto\BankAccount\BankAccountDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class BankAccountsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Main account',
            'currency' => 'EUR',
            'balance' => '100.15',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 42],
            'journal' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/journals/7'],
            'ledger_account' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/8'],
        ];
    }

    public function testListReturnsACursorPageOfBankAccountDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->bankAccounts()->list();

        self::assertCount(1, $page->items);
        self::assertInstanceOf(BankAccountDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
        self::assertSame(Currency::Eur, $page->items[0]->currency);
    }

    public function testGetReturnsABankAccountDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $pennylane->withMockClient($mockClient);

        $bankAccount = $pennylane->bankAccounts()->get(42);

        self::assertSame(42, $bankAccount->id);
        self::assertSame('Main account', $bankAccount->name);
    }

    public function testCreateReturnsABankAccountDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $bankAccount = $pennylane->bankAccounts()->create(
            name: 'Main account',
            bankEstablishmentId: 42,
            currency: Currency::Eur,
            accountType: AccountType::Checking,
        );

        self::assertSame(42, $bankAccount->id);
        self::assertSame('Main account', $bankAccount->name);
    }
}
