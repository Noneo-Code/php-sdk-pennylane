<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\UpdateLedgerEntryLine;
use PHPUnit\Framework\TestCase;

final class UpdateLedgerEntryLineTest extends TestCase
{
    public function testItConvertsToTheApiShapeForRequestBodies(): void
    {
        $line = new UpdateLedgerEntryLine(id: 42, debit: '100.00', credit: '0.00', ledgerAccountId: 421, label: 'Transaction label');

        self::assertSame([
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'ledger_account_id' => 421,
            'label' => 'Transaction label',
        ], $line->toArray());
    }

    public function testItOnlyKeepsTheIdWhenNothingElseIsProvided(): void
    {
        $line = new UpdateLedgerEntryLine(id: 42);

        self::assertSame(['id' => 42], $line->toArray());
    }
}
