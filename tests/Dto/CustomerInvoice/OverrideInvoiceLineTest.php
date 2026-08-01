<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\OverrideInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use PHPUnit\Framework\TestCase;

final class OverrideInvoiceLineTest extends TestCase
{
    public function testToArraySerializesTheRequiredFields(): void
    {
        $line = new OverrideInvoiceLine(
            currencyAmount: '50.00',
            currencyTax: '10.00',
            vatRate: VatRate::Fr200,
            ledgerAccountId: 706,
            quantity: 1,
            rawCurrencyUnitPrice: '50.00',
        );

        self::assertSame([
            'currency_amount' => '50.00',
            'currency_tax' => '10.00',
            'vat_rate' => 'FR_200',
            'ledger_account_id' => 706,
            'quantity' => 1,
            'raw_currency_unit_price' => '50.00',
        ], $line->toArray());
    }

    public function testToArrayIncludesTheOptionalProductId(): void
    {
        $line = new OverrideInvoiceLine(
            currencyAmount: '50.00',
            currencyTax: '10.00',
            vatRate: VatRate::Fr200,
            ledgerAccountId: 706,
            quantity: 1,
            rawCurrencyUnitPrice: '50.00',
            productId: 42,
        );

        self::assertSame(42, $line->toArray()['product_id']);
    }
}
