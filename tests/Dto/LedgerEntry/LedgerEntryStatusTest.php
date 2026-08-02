<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryStatus;
use PHPUnit\Framework\TestCase;

final class LedgerEntryStatusTest extends TestCase
{
    public function testItExposesTheDocumentedAccountingStates(): void
    {
        self::assertSame('accounting_needed', LedgerEntryStatus::AccountingNeeded->value);
        self::assertSame('archived', LedgerEntryStatus::Archived->value);
        self::assertSame('complete', LedgerEntryStatus::Complete->value);
        self::assertSame('draft', LedgerEntryStatus::Draft->value);
        self::assertSame('entry', LedgerEntryStatus::Entry->value);
        self::assertSame('validation_needed', LedgerEntryStatus::ValidationNeeded->value);
        self::assertSame('waiting_details', LedgerEntryStatus::WaitingDetails->value);
    }
}
