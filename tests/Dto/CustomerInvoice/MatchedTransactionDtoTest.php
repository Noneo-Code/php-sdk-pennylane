<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\MatchedTransactionDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class MatchedTransactionDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getCustomerInvoiceMatchedTransactions', pointer: 'items')]
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
            'currency_fee' => '120.00',
            'fee' => '120.00',
            'journal' => ['id' => 42],
            'bank_account' => ['id' => 53, 'url' => 'https://app.pennylane.com/api/external/v2/bank_accounts/53'],
            'pro_account_expense' => null,
            'customer' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/customers/42'],
            'categories' => [[
                'id' => 421,
                'label' => 'HR - Salaries',
                'weight' => '0.25',
                'category_group' => ['id' => 229],
                'analytical_code' => 'CODE123',
                'created_at' => '2023-08-30T10:08:08.146343Z',
                'updated_at' => '2023-08-30T10:08:08.146343Z',
            ]],
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $transaction = MatchedTransactionDto::fromArray(self::documentedPayload());

        self::assertSame(42, $transaction->id);
        self::assertSame('VIR SEPA MY SUPPLIER SAS', $transaction->label);
        self::assertTrue($transaction->attachmentRequired);
        self::assertSame(Currency::Eur, $transaction->currency);
        self::assertSame(42, $transaction->journalId);
        self::assertSame(53, $transaction->bankAccountId);
        self::assertSame('https://app.pennylane.com/api/external/v2/bank_accounts/53', $transaction->bankAccountUrl);
        self::assertSame(42, $transaction->customerId);
        self::assertCount(1, $transaction->categories);
        self::assertInstanceOf(CategoryDto::class, $transaction->categories[0]);
        self::assertSame(421, $transaction->categories[0]->id);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['label'] = null;
        $payload['currency'] = null;
        $payload['customer'] = null;

        $transaction = MatchedTransactionDto::fromArray($payload);

        self::assertNull($transaction->label);
        self::assertNull($transaction->currency);
        self::assertNull($transaction->customerId);
        self::assertNull($transaction->customerUrl);
    }
}
