<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\LedgerEntries;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\PostLedgerEntries;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostLedgerEntriesTest extends TestCase
{
    public function testSendsAPostRequestWithTheMinimalPayload(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostLedgerEntries(
            date: '2023-08-30',
            label: 'Payment for Services',
            journalId: 123,
            ledgerEntryLines: [
                new NewLedgerEntryLine(debit: '100.00', credit: '0.00', ledgerAccountId: 987, label: 'Transaction label'),
                new NewLedgerEntryLine(debit: '0.00', credit: '100.00', ledgerAccountId: 421),
            ],
        ));

        $mockClient->assertSent(function (PostLedgerEntries $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/ledger_entries' === $sent->resolveEndpoint()
                && [
                    'date' => '2023-08-30',
                    'label' => 'Payment for Services',
                    'journal_id' => 123,
                    'ledger_entry_lines' => [
                        ['debit' => '100.00', 'credit' => '0.00', 'ledger_account_id' => 987, 'label' => 'Transaction label'],
                        ['debit' => '0.00', 'credit' => '100.00', 'ledger_account_id' => 421],
                    ],
                ] === $sent->body()->all();
        });
    }

    public function testForwardsAllTheOptionalFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PostLedgerEntries(
            date: '2023-08-30',
            label: 'Payment for Services',
            journalId: 123,
            ledgerEntryLines: [new NewLedgerEntryLine(debit: '100.00', credit: '0.00', ledgerAccountId: 987)],
            dueDate: '2023-09-30',
            fileAttachmentId: 42,
            currency: Currency::Eur,
            pieceNumber: 'TENSNYLUGV',
        ));

        $mockClient->assertSent(function (PostLedgerEntries $sent): bool {
            $body = $sent->body()->all();

            return '2023-09-30' === $body['due_date']
                && 42 === $body['file_attachment_id']
                && 'EUR' === $body['currency']
                && 'TENSNYLUGV' === $body['piece_number'];
        });
    }
}
