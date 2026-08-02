<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Changelog;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogOperation;
use Noneo\Sdk\Pennylane\Dto\Changelog\LedgerEntryLineCategoryChangelogEntryDto;
use PHPUnit\Framework\TestCase;

final class LedgerEntryLineCategoryChangelogEntryDtoTest extends TestCase
{
    public function testBuildsFromTheDocumentedPayload(): void
    {
        $dto = LedgerEntryLineCategoryChangelogEntryDto::fromArray([
            'id' => 123,
            'operation' => 'insert',
            'ledger_entry_line' => ['id' => 789],
            'ledger_entry' => ['id' => 456, 'type' => 'general'],
            'processed_at' => '2025-06-25T11:54:18.589480Z',
            'updated_at' => '2025-06-25T11:54:18.589480Z',
            'created_at' => '2025-06-25T11:54:18.589480Z',
        ]);

        self::assertSame(123, $dto->id);
        self::assertSame(ChangelogOperation::Insert, $dto->operation);
        self::assertSame(789, $dto->ledgerEntryLineId);
        self::assertSame(456, $dto->ledgerEntryId);
        self::assertSame('general', $dto->ledgerEntryType);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->processedAt);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->updatedAt);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->createdAt);
    }

    public function testLedgerEntryLineAndLedgerEntryCanBeNullWhenUncategorizedAndDeletedTogether(): void
    {
        $dto = LedgerEntryLineCategoryChangelogEntryDto::fromArray([
            'id' => 123,
            'operation' => 'delete',
            'ledger_entry_line' => null,
            'ledger_entry' => null,
            'processed_at' => '2025-06-25T11:54:18.589480Z',
            'updated_at' => '2025-06-25T11:54:18.589480Z',
            'created_at' => '2025-06-25T11:54:18.589480Z',
        ]);

        self::assertNull($dto->ledgerEntryLineId);
        self::assertNull($dto->ledgerEntryId);
        self::assertNull($dto->ledgerEntryType);
    }
}
