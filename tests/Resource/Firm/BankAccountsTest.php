<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\BankAccount\BankAccountDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\BankAccounts\PostBankAccount;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class BankAccountsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Main account',
            'currency' => 'EUR',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 42],
            'journal' => ['id' => 7, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals/7'],
            'ledger_account' => ['id' => 8, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts/8'],
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->bankAccounts()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/bank_accounts' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(BankAccountDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
        self::assertNull($page->items[0]->balance);
    }

    public function testCreateSendsAPostRequestWithTheBankAccountBody(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->bankAccounts()->create('Main account');

        $mockClient->assertSent(function (PostBankAccount $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/bank_accounts' === $response->getPendingRequest()->getUrl()
                && ['name' => 'Main account'] === $sent->body()->all();
        });
    }
}
