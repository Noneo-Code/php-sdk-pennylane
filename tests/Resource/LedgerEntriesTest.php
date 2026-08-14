<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryLineSummaryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\LedgerEntries\GetLedgerEntries;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class LedgerEntriesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedListItemPayload(): array
    {
        return [
            'id' => 1,
            'created_at' => '2023-06-14T12:12:56.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'label' => 'Payment for Services',
            'piece_number' => 'TENSNYLUGV',
            'date' => '2023-08-30',
            'due_date' => '2023-08-30',
            'invoice_number' => 'FAC-2024-001',
            'journal_id' => 123,
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/123'],
            'status' => 'complete',
            'categories' => [],
            'ledger_attachment_filename' => null,
            'attachment' => null,
        ];
    }

    public function testListReturnsACursorPageOfLedgerEntryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedListItemPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->ledgerEntries()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertInstanceOf(LedgerEntryDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testListForwardsFilterAndSort(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->ledgerEntries()->list(filters: FilterSet::make()->eq('journal_id', 123));

        $mockClient->assertSent(function (GetLedgerEntries $sent, Response $response): bool {
            return '[{"field":"journal_id","operator":"eq","value":123}]' === ($response->getPendingRequest()->query()->all()['filter'] ?? null);
        });
    }

    public function testGetReturnsALedgerEntryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'id' => 1,
                'label' => 'Payment for Services',
                'piece_number' => 'TENSNYLUGV',
                'date' => '2023-08-30',
                'due_date' => '2023-08-30',
                'invoice_number' => 'FAC-2024-001',
                'created_at' => '2023-06-14T12:12:56.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
                'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/123'],
                'status' => 'complete',
                'categories' => [],
                'attachment' => null,
                'ledger_entry_lines' => [],
            ]),
        ]));

        $ledgerEntry = $pennylane->ledgerEntries()->get(1);

        self::assertInstanceOf(LedgerEntryDto::class, $ledgerEntry);
        self::assertSame(1, $ledgerEntry->id);
        self::assertSame([], $ledgerEntry->ledgerEntryLines);
    }

    public function testCreateReturnsALedgerEntryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make([
                'id' => 1,
                'label' => 'Payment for Services',
                'piece_number' => 'TENSNYLUGV',
                'date' => '2023-08-30',
                'due_date' => null,
                'invoice_number' => null,
                'journal_id' => 123,
                'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/123'],
                'status' => 'draft',
                'categories' => [],
                'ledger_attachment_filename' => null,
                'ledger_attachment_id' => null,
                'attachment' => null,
                'ledger_entry_lines' => [
                    [
                        'id' => 42,
                        'debit' => '100.00',
                        'credit' => '0.00',
                        'ledger_account_id' => 987,
                        'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
                        'label' => 'Transaction label',
                    ],
                ],
            ], 201),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $ledgerEntry = $pennylane->ledgerEntries()->create(
            date: '2023-08-30',
            label: 'Payment for Services',
            journalId: 123,
            ledgerEntryLines: [new NewLedgerEntryLine(debit: '100.00', credit: '0.00', ledgerAccountId: 987, label: 'Transaction label')],
        );

        self::assertInstanceOf(LedgerEntryDto::class, $ledgerEntry);
        self::assertNotNull($ledgerEntry->ledgerEntryLines);
        self::assertInstanceOf(LedgerEntryLineSummaryDto::class, $ledgerEntry->ledgerEntryLines[0]);
    }

    public function testUpdateReturnsALedgerEntryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make([
                'id' => 1,
                'label' => 'Nouveau libellé',
                'piece_number' => 'TENSNYLUGV',
                'date' => '2023-08-30',
                'due_date' => null,
                'invoice_number' => null,
                'journal_id' => 123,
                'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/123'],
                'status' => 'draft',
                'categories' => [],
                'ledger_attachment_filename' => null,
                'ledger_attachment_id' => null,
                'attachment' => null,
                'ledger_entry_lines' => [],
            ]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $ledgerEntry = $pennylane->ledgerEntries()->update(1, label: 'Nouveau libellé');

        self::assertSame('Nouveau libellé', $ledgerEntry->label);
    }

    public function testLinesReturnsACursorPageOfLedgerEntryLineSummaryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [
                    [
                        'id' => 42,
                        'debit' => '100.00',
                        'credit' => '0.00',
                        'label' => 'Employees - Jean Dupont / Salary May 2023',
                        'ledger_account_id' => 987,
                        'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
                    ],
                ],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->ledgerEntries()->lines(123001);

        self::assertInstanceOf(LedgerEntryLineSummaryDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testPaginateYieldsLedgerEntryDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedListItemPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [['id' => 2, 'label' => 'Autre écriture', 'piece_number' => null, 'date' => null, 'due_date' => null, 'invoice_number' => null, 'journal' => ['id' => 1, 'url' => 'x'], 'status' => null, 'categories' => [], 'attachment' => null, 'created_at' => 'x', 'updated_at' => 'x']],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $ledgerEntries = iterator_to_array($pennylane->ledgerEntries()->paginate());

        self::assertCount(2, $ledgerEntries);
        self::assertSame(1, $ledgerEntries[0]->id);
        self::assertSame(2, $ledgerEntries[1]->id);
    }
}
