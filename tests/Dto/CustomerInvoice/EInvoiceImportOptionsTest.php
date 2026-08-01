<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceLineOptions;
use PHPUnit\Framework\TestCase;

final class EInvoiceImportOptionsTest extends TestCase
{
    public function testToArraySerializesTheCustomerIdAndInvoiceLines(): void
    {
        $options = new EInvoiceImportOptions(
            customerId: 12,
            invoiceLines: [new EInvoiceLineOptions(eInvoiceLineId: 'BT-126-1', ledgerAccountId: 123, productId: 456)],
        );

        self::assertSame([
            'customer_id' => 12,
            'invoice_lines' => [
                ['e_invoice_line_id' => 'BT-126-1', 'ledger_account_id' => 123, 'product_id' => 456],
            ],
        ], $options->toArray());
    }

    public function testToArrayOmitsAbsentFields(): void
    {
        $options = new EInvoiceImportOptions();

        self::assertSame([], $options->toArray());
    }
}
