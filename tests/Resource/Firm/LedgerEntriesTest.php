<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource\Firm;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\PennylaneFirm;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PostLedgerEntries;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PutLedgerEntries;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class LedgerEntriesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 1,
            'label' => 'Achat fournitures',
            'piece_number' => null,
            'date' => '2023-08-30',
            'due_date' => null,
            'invoice_number' => null,
            'journal' => ['id' => 7, 'url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1234/journals/7'],
            'status' => 'draft',
            'categories' => [],
            'attachment' => null,
        ];
    }

    public function testListSendsAGetRequestScopedToTheCompany(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make(['items' => [$this->documentedPayload()], 'has_more' => false, 'next_cursor' => null])]);
        $firm->withMockClient($mockClient);

        $page = $firm->company(1234)->ledgerEntries()->list();

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries' === $response->getPendingRequest()->getUrl();
        });

        self::assertInstanceOf(LedgerEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testGetSendsAGetRequestToTheLedgerEntryEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->ledgerEntries()->get(1);

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries/1' === $response->getPendingRequest()->getUrl();
        });
    }

    public function testCreateSendsAPostRequestWithTheLedgerEntryBody(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->ledgerEntries()->create(
            date: '2023-08-30',
            label: 'Achat fournitures',
            journalId: 7,
            ledgerEntryLines: [new NewLedgerEntryLine('100.00', '0.00', 512)],
        );

        $mockClient->assertSent(function (PostLedgerEntries $sent, Response $response): bool {
            $body = $sent->body()->all();

            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries' === $response->getPendingRequest()->getUrl()
                && 'Achat fournitures' === ($body['label'] ?? null)
                && 7 === ($body['journal_id'] ?? null);
        });
    }

    public function testUpdateSendsAPutRequestToTheLedgerEntryEndpoint(): void
    {
        $firm = new PennylaneFirm(new TokenAuthenticator('firm-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPayload())]);
        $firm->withMockClient($mockClient);

        $firm->company(1234)->ledgerEntries()->update(1, label: 'Achat corrigé');

        $mockClient->assertSent(function (PutLedgerEntries $sent, Response $response): bool {
            return 'https://app.pennylane.com/api/external/firm/v1/companies/1234/ledger_entries/1' === $response->getPendingRequest()->getUrl()
                && ['label' => 'Achat corrigé'] === $sent->body()->all();
        });
    }
}
