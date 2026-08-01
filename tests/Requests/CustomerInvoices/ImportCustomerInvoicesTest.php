<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewImportedInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\ImportCustomerInvoices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ImportCustomerInvoicesTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ImportCustomerInvoices(
            fileAttachmentId: 42,
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            invoiceLines: [
                new NewImportedInvoiceLine(
                    currencyAmount: '120.00',
                    currencyTax: '20.00',
                    quantity: 12,
                    rawCurrencyUnitPrice: '33.333334',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                ),
            ],
        ));

        $mockClient->assertSent(function (ImportCustomerInvoices $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/import' === $sent->resolveEndpoint()
                && [
                    'file_attachment_id' => 42,
                    'date' => '2023-08-30',
                    'deadline' => '2023-09-30',
                    'customer_id' => 42,
                    'currency_amount_before_tax' => '100.00',
                    'currency_amount' => '120.00',
                    'currency_tax' => '20.00',
                    'invoice_lines' => [[
                        'currency_amount' => '120.00',
                        'currency_tax' => '20.00',
                        'quantity' => 12,
                        'raw_currency_unit_price' => '33.333334',
                        'unit' => 'piece',
                        'vat_rate' => 'FR_200',
                    ]],
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ImportCustomerInvoices(
            fileAttachmentId: 42,
            date: '2023-08-30',
            deadline: '2023-09-30',
            customerId: 42,
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            invoiceLines: [],
            importAsIncomplete: true,
            invoiceNumber: 'F20230001',
            currency: Currency::Usd,
            amount: '120.00',
            tax: '20.00',
            label: 'Custom accounting label',
            convertToEInvoice: true,
            externalReference: 'FR123',
        ));

        $mockClient->assertSent(function (ImportCustomerInvoices $sent): bool {
            $body = $sent->body()->all();

            return true === $body['import_as_incomplete']
                && 'F20230001' === $body['invoice_number']
                && 'USD' === $body['currency']
                && '120.00' === $body['amount']
                && '20.00' === $body['tax']
                && 'Custom accounting label' === $body['label']
                && true === $body['convert_to_e_invoice']
                && 'FR123' === $body['external_reference']
                && [] === $body['invoice_lines'];
        });
    }
}
