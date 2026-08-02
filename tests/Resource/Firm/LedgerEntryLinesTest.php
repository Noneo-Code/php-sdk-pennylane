<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\LedgerEntryLineDto;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class LedgerEntryLinesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'label' => 'Salaire',
            'categories' => [],
            'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_accounts/987'],
            'journal' => ['id' => 7, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals/7'],
            'date' => '2023-08-30',
            'ledger_entry' => ['id' => 1],
            'lettered_ledger_entry_lines' => [
                'ids' => [],
                'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entry_lines/42/lettered_ledger_entry_lines',
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->ledgerEntryLines()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entry_lines' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(LedgerEntryLineDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testGetSendsAGetRequestToTheLedgerEntryLineEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->ledgerEntryLines()->get(42);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entry_lines/42' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testLetteredSendsAGetRequestToTheLetteredSubResource(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $payload = $this->documentedPayload();
        unset($payload['categories']);
        $mockClient = new MockClient([MockResponse::make(['items' => [$payload], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->ledgerEntryLines()->lettered(42);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entry_lines/42/lettered_ledger_entry_lines' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(LedgerEntryLineDto::class, $page->items[0]);
        self::assertSame([], $page->items[0]->categories);
    }
}
