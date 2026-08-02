<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\NewLedgerEntryLine;
use PHPUnit\Framework\TestCase;

final class NewLedgerEntryLineTest extends TestCase
{
    public function testItConvertsToTheApiShapeForRequestBodies(): void
    {
        $line = new NewLedgerEntryLine(debit: '100.00', credit: '0.00', ledgerAccountId: 987, label: 'Transaction label');

        self::assertSame([
            'debit' => '100.00',
            'credit' => '0.00',
            'ledger_account_id' => 987,
            'label' => 'Transaction label',
        ], $line->toArray());
    }

    public function testItOmitsTheLabelWhenNotProvided(): void
    {
        $line = new NewLedgerEntryLine(debit: '100.00', credit: '0.00', ledgerAccountId: 987);

        self::assertSame([
            'debit' => '100.00',
            'credit' => '0.00',
            'ledger_account_id' => 987,
        ], $line->toArray());
    }
}
