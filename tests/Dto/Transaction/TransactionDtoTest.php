<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Transaction;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\Transaction\TransactionDto;
use PHPUnit\Framework\TestCase;

final class TransactionDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'VIR SEPA MY SUPPLIER SAS',
            'attachment_required' => true,
            'date' => '2023-08-30',
            'outstanding_balance' => '49.3',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'archived_at' => null,
            'currency' => 'EUR',
            'currency_amount' => '120.00',
            'amount' => '120.00',
            'currency_fee' => '12.00',
            'fee' => '12.00',
            'journal' => ['id' => 234, 'url' => 'https://app.pennylane.com/api/external/v2/journals/67'],
            'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/v2/bank_accounts/53'],
            'pro_account_expense' => null,
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'supplier' => null,
            'categories' => [
                ['id' => 421, 'label' => 'HR - Salaries', 'weight' => '0.25', 'category_group' => ['id' => 229], 'analytical_code' => 'CODE123', 'created_at' => '2023-08-30T10:08:08.146343Z', 'updated_at' => '2023-08-30T10:08:08.146343Z'],
            ],
            'matched_invoices' => ['url' => 'https://app.pennylane.com/api/external/v2/transactions/42/matched_invoices'],
            'interbank_code' => 'B1D',
        ];
    }

    public function testFromArrayMapsTheDocumentedPayload(): void
    {
        $dto = TransactionDto::fromArray($this->documentedPayload());

        self::assertSame(42, $dto->id);
        self::assertSame('VIR SEPA MY SUPPLIER SAS', $dto->label);
        self::assertTrue($dto->attachmentRequired);
        self::assertSame('2023-08-30', $dto->date);
        self::assertSame(Currency::Eur, $dto->currency);
        self::assertSame(234, $dto->journalId);
        self::assertSame(53, $dto->bankAccountId);
        self::assertSame(42, $dto->customerId);
        self::assertNull($dto->supplierId);
        self::assertCount(1, $dto->categories);
        self::assertSame(421, $dto->categories[0]->id);
        self::assertSame('https://app.pennylane.com/api/external/v2/transactions/42/matched_invoices', $dto->matchedInvoicesUrl);
        self::assertSame('B1D', $dto->interbankCode);
    }

    public function testFromArrayAllowsANullCurrency(): void
    {
        $payload = $this->documentedPayload();
        $payload['currency'] = null;

        $dto = TransactionDto::fromArray($payload);

        self::assertNull($dto->currency);
    }

    public function testFromArrayAllowsAMissingMatchedInvoicesAsReturnedByTheFirmApi(): void
    {
        $payload = $this->documentedPayload();
        unset($payload['matched_invoices']);

        $dto = TransactionDto::fromArray($payload);

        self::assertNull($dto->matchedInvoicesUrl);
    }
}
