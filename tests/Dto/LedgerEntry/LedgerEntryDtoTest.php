<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryStatus;
use PHPUnit\Framework\TestCase;

final class LedgerEntryDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedListItemPayload(): void
    {
        $dto = LedgerEntryDto::fromArray([
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
        ]);

        self::assertSame(1, $dto->id);
        self::assertSame('Payment for Services', $dto->label);
        self::assertSame('TENSNYLUGV', $dto->pieceNumber);
        self::assertSame('2023-08-30', $dto->date);
        self::assertSame(123, $dto->journal->id);
        self::assertSame(LedgerEntryStatus::Complete, $dto->status);
        self::assertSame([], $dto->categories);
        self::assertNull($dto->attachment);
        self::assertNull($dto->ledgerEntryLines);
        self::assertSame('2023-06-14T12:12:56.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItBuildsFromTheDocumentedCreateResponsePayload(): void
    {
        $dto = LedgerEntryDto::fromArray([
            'id' => 1,
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
        ]);

        self::assertNull($dto->createdAt);
        self::assertNull($dto->updatedAt);
        self::assertNotNull($dto->ledgerEntryLines);
        self::assertCount(1, $dto->ledgerEntryLines);
        self::assertSame(42, $dto->ledgerEntryLines[0]->id);
    }

    public function testItBuildsFromTheDocumentedGetResponsePayload(): void
    {
        $dto = LedgerEntryDto::fromArray([
            'id' => 1,
            'label' => 'Payment for Services',
            'piece_number' => 'TENSNYLUGV',
            'date' => '2023-08-30',
            'due_date' => '2023-08-30',
            'invoice_number' => 'FAC-2024-001',
            'created_at' => '2023-06-14T12:12:56.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/123'],
            'status' => null,
            'categories' => [],
            'attachment' => [
                'filename' => 'filename.pdf',
                'url' => 'http://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/filename.pdf',
            ],
            'ledger_entry_lines' => [],
        ]);

        self::assertNull($dto->status);
        self::assertNotNull($dto->attachment);
        self::assertSame('filename.pdf', $dto->attachment->filename);
        self::assertSame([], $dto->ledgerEntryLines);
    }
}
