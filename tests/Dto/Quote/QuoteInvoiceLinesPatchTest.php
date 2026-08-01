<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\NewQuoteStandardInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\Quote\UpdateQuoteInvoiceLine;
use PHPUnit\Framework\TestCase;

final class QuoteInvoiceLinesPatchTest extends TestCase
{
    public function testItSerializesCreateUpdateAndDelete(): void
    {
        $patch = new QuoteInvoiceLinesPatch(
            create: [new NewQuoteProductInvoiceLine(productId: 3049, quantity: 12)],
            update: [new UpdateQuoteInvoiceLine(id: 1, label: 'Updated label')],
            delete: [2],
        );

        self::assertSame([
            'create' => [['product_id' => 3049, 'quantity' => 12]],
            'update' => [['id' => 1, 'label' => 'Updated label']],
            'delete' => [['id' => 2]],
        ], $patch->toArray());
    }

    public function testItOmitsEmptyLists(): void
    {
        self::assertSame([], (new QuoteInvoiceLinesPatch())->toArray());
    }

    public function testStandardLineSerializesAllRequiredFields(): void
    {
        $line = new NewQuoteStandardInvoiceLine(
            label: 'Consulting fees',
            rawCurrencyUnitPrice: '33.333334',
            unit: 'piece',
            vatRate: VatRate::Fr200,
            quantity: 2,
        );

        self::assertSame([
            'label' => 'Consulting fees',
            'quantity' => 2,
            'raw_currency_unit_price' => '33.333334',
            'unit' => 'piece',
            'vat_rate' => 'FR_200',
        ], $line->toArray());
    }
}
