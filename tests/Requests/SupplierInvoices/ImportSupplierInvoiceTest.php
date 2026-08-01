<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\NewSupplierInvoiceLine;
use Noneo\Sdk\Pennylane\Exception\ConflictException;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\ImportSupplierInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ImportSupplierInvoiceTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 123], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ImportSupplierInvoice(
            fileAttachmentId: 123,
            supplierId: 456,
            date: '2021-01-30',
            deadline: '2021-01-30',
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            invoiceLines: [
                new NewSupplierInvoiceLine(currencyAmount: '120.00', currencyTax: '20.00', vatRate: VatRate::Fr200),
            ],
        ));

        $mockClient->assertSent(function (ImportSupplierInvoice $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/supplier_invoices/import' === $sent->resolveEndpoint()
                && [
                    'file_attachment_id' => 123,
                    'supplier_id' => 456,
                    'date' => '2021-01-30',
                    'deadline' => '2021-01-30',
                    'currency_amount_before_tax' => '100.00',
                    'currency_amount' => '120.00',
                    'currency_tax' => '20.00',
                    'invoice_lines' => [[
                        'currency_amount' => '120.00',
                        'currency_tax' => '20.00',
                        'vat_rate' => 'FR_200',
                    ]],
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 123], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new ImportSupplierInvoice(
            fileAttachmentId: 123,
            supplierId: 456,
            date: '2021-01-30',
            deadline: '2021-01-30',
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            invoiceLines: [],
            importAsIncomplete: true,
            invoiceNumber: 'F20230001',
            label: 'Custom accounting label',
            externalReference: 'FR123',
        ));

        $mockClient->assertSent(function (ImportSupplierInvoice $sent): bool {
            $body = $sent->body()->all();

            return true === $body['import_as_incomplete']
                && 'F20230001' === $body['invoice_number']
                && 'Custom accounting label' === $body['label']
                && 'FR123' === $body['external_reference'];
        });
    }

    public function testADuplicateFileAttachmentThrowsAConflictException(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['error' => 'Invoice already exists with id: 42', 'status' => 409], 409)]);
        $pennylane->withMockClient($mockClient);

        try {
            $pennylane->send(new ImportSupplierInvoice(
                fileAttachmentId: 123,
                supplierId: 456,
                date: '2021-01-30',
                deadline: '2021-01-30',
                currencyAmountBeforeTax: '100.00',
                currencyAmount: '120.00',
                currencyTax: '20.00',
                invoiceLines: [],
            ));
            self::fail('Une ConflictException était attendue.');
        } catch (ConflictException $exception) {
            self::assertSame(42, $exception->existingId());
        }
    }
}
