<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class MatchedTransactionDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getSupplierInvoiceMatchedTransactions', pointer: 'items')]
    public static function documentedPayload(): array
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
            'currency_fee' => null,
            'fee' => null,
            'journal' => ['id' => 42],
            'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/v2/bank_accounts/53'],
            'pro_account_expense' => null,
            'supplier' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/supplier/42'],
            'categories' => [],
        ];
    }

    public function testFromArrayBuildsAMatchedTransactionDtoFromTheDocumentedPayload(): void
    {
        $transaction = MatchedTransactionDto::fromArray($this->documentedPayload());

        self::assertSame(42, $transaction->id);
        self::assertSame('VIR SEPA MY SUPPLIER SAS', $transaction->label);
        self::assertTrue($transaction->attachmentRequired);
        self::assertSame(Currency::Eur, $transaction->currency);
        self::assertSame(42, $transaction->journalId);
        self::assertSame(53, $transaction->bankAccountId);
        self::assertNull($transaction->proAccountExpense);
        self::assertSame(42, $transaction->supplierId);
        self::assertSame('https://app.pennylane.com/api/external/v2/supplier/42', $transaction->supplierUrl);
        self::assertSame([], $transaction->categories);
    }

    public function testFromArrayParsesTheProAccountExpense(): void
    {
        $payload = $this->documentedPayload();
        $payload['pro_account_expense'] = [
            'employee' => ['id' => 42, 'first_name' => 'John', 'last_name' => 'Doe'],
            'card_masked_number' => '123456XXXXXX7890',
        ];

        $transaction = MatchedTransactionDto::fromArray($payload);

        self::assertNotNull($transaction->proAccountExpense);
        self::assertSame(42, $transaction->proAccountExpense->employeeId);
        self::assertSame('John', $transaction->proAccountExpense->employeeFirstName);
        self::assertSame('123456XXXXXX7890', $transaction->proAccountExpense->cardMaskedNumber);
    }
}
