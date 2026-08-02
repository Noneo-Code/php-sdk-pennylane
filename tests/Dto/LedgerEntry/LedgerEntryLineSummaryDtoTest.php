<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryLineSummaryDto;
use PHPUnit\Framework\TestCase;

final class LedgerEntryLineSummaryDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedEmbeddedLedgerEntryLinePayload(): void
    {
        $dto = LedgerEntryLineSummaryDto::fromArray([
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'ledger_account_id' => 987,
            'ledger_account' => [
                'id' => 987,
                'number' => '411000',
                'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42',
            ],
            'label' => 'Transaction label',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame('100.00', $dto->debit);
        self::assertSame('0.00', $dto->credit);
        self::assertSame('Transaction label', $dto->label);
        self::assertSame(987, $dto->ledgerAccount->id);
        self::assertSame('411000', $dto->ledgerAccount->number);
    }
}
