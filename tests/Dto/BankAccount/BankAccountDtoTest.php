<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\BankAccount;

use Noneo\Sdk\Pennylane\Dto\BankAccount\BankAccountDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class BankAccountDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getBankAccount')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 42,
            'name' => 'Main account',
            'currency' => 'EUR',
            'balance' => '100.15',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'bank_establishment' => ['id' => 42],
            'journal' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/journals/7'],
            'ledger_account' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/ledger_accounts/8'],
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = BankAccountDto::fromArray($this->documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('Main account', $dto->name);
        self::assertSame(Currency::Eur, $dto->currency);
        self::assertSame('100.15', $dto->balance);
        self::assertSame(42, $dto->bankEstablishmentId);
        self::assertSame(42, $dto->journalId);
        self::assertSame('https://app.pennylane.com/api/external/v2/journals/7', $dto->journalUrl);
        self::assertSame(42, $dto->ledgerAccountId);
        self::assertSame('https://app.pennylane.com/api/external/v2/ledger_accounts/8', $dto->ledgerAccountUrl);
    }

    public function testFromArrayAllowsANullJournal(): void
    {
        $payload = $this->documentedPayload();
        $payload['journal'] = null;

        $dto = BankAccountDto::fromArray($payload);

        self::assertNull($dto->journalId);
        self::assertNull($dto->journalUrl);
    }

    public function testFromArrayAllowsAMissingBalanceAsReturnedByTheFirmApi(): void
    {
        $payload = $this->documentedPayload();
        unset($payload['balance']);

        $dto = BankAccountDto::fromArray($payload);

        self::assertNull($dto->balance);
    }
}
