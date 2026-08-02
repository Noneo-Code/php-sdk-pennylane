<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\LedgerEntryLineDto;
use PHPUnit\Framework\TestCase;

final class LedgerEntryLineDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedLedgerEntryLinePayload(): void
    {
        $dto = LedgerEntryLineDto::fromArray([
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'label' => 'Employees - Jean Dupont / Salary May 2023',
            'categories' => [],
            'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/42'],
            'date' => '2023-08-30',
            'ledger_entry' => ['id' => 123001],
            'lettered_ledger_entry_lines' => [
                'ids' => [42, 1271004],
                'url' => 'https://app.pennylane.com/api/external/v2/ledger_entry_lines/42/lettered_ledger_entry_lines',
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame('100.00', $dto->debit);
        self::assertSame('0.00', $dto->credit);
        self::assertSame([], $dto->categories);
        self::assertSame(987, $dto->ledgerAccount->id);
        self::assertSame(123, $dto->journal->id);
        self::assertSame('2023-08-30', $dto->date);
        self::assertSame(123001, $dto->ledgerEntryId);
        self::assertSame([42, 1271004], $dto->letteredLedgerEntryLineIds);
        self::assertSame('https://app.pennylane.com/api/external/v2/ledger_entry_lines/42/lettered_ledger_entry_lines', $dto->letteredLedgerEntryLinesUrl);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame([], $dto->imputationDates);
    }

    public function testItDefaultsCategoriesToAnEmptyArrayAsReturnedByTheFirmLetteredLedgerEntryLinesEndpoint(): void
    {
        $dto = LedgerEntryLineDto::fromArray([
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'label' => 'Employees - Jean Dupont / Salary May 2023',
            'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/42'],
            'date' => '2023-08-30',
            'ledger_entry' => ['id' => 123001],
            'lettered_ledger_entry_lines' => [
                'ids' => [],
                'url' => 'https://app.pennylane.com/api/external/v2/ledger_entry_lines/42/lettered_ledger_entry_lines',
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame([], $dto->categories);
    }

    public function testItParsesTheImputationDatesReturnedByTheFirmApi(): void
    {
        $dto = LedgerEntryLineDto::fromArray([
            'id' => 42,
            'debit' => '100.00',
            'credit' => '0.00',
            'label' => 'Employees - Jean Dupont / Salary May 2023',
            'categories' => [],
            'ledger_account' => ['id' => 987, 'number' => '411000', 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/42'],
            'journal' => ['id' => 123, 'url' => 'https://app.pennylane.com/api/external/v2/journals/42'],
            'date' => '2023-08-30',
            'ledger_entry' => ['id' => 123001],
            'lettered_ledger_entry_lines' => [
                'ids' => [],
                'url' => 'https://app.pennylane.com/api/external/v2/ledger_entry_lines/42/lettered_ledger_entry_lines',
            ],
            'imputation_dates' => [
                ['start_date' => '2023-01-01', 'end_date' => '2023-01-31'],
                ['start_date' => null, 'end_date' => null],
            ],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertCount(2, $dto->imputationDates);
        self::assertSame('2023-01-01', $dto->imputationDates[0]->startDate);
        self::assertSame('2023-01-31', $dto->imputationDates[0]->endDate);
        self::assertNull($dto->imputationDates[1]->startDate);
        self::assertNull($dto->imputationDates[1]->endDate);
    }
}
