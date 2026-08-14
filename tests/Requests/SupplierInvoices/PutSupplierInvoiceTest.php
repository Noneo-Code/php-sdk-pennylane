<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\NewSupplierInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\UpdateSupplierInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSupplierInvoiceTest extends TestCase
{
    public function testSendsAPutRequestWithTheProvidedFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 123])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutSupplierInvoice(
            id: 123,
            supplierId: 456,
            date: '2021-01-30',
            currency: Currency::Eur,
            currencyAmount: '120.00',
            currencyTax: '20.00',
            externalReference: 'FR123',
        ));

        $mockClient->assertSent(function (PutSupplierInvoice $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/supplier_invoices/123' === $sent->resolveEndpoint()
                && [
                    'supplier_id' => 456,
                    'date' => '2021-01-30',
                    'currency' => 'EUR',
                    'currency_amount' => '120.00',
                    'currency_tax' => '20.00',
                    'external_reference' => 'FR123',
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheInvoiceLinesPatch(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 123])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutSupplierInvoice(
            id: 123,
            invoiceLines: new SupplierInvoiceLinesPatch(
                create: [new NewSupplierInvoiceLine(currencyAmount: '120.00', currencyTax: '20.00', vatRate: VatRate::Fr200, label: 'Demo label')],
                update: [new UpdateSupplierInvoiceLine(id: 1, label: 'Updated label')],
                delete: [2],
            ),
        ));

        $mockClient->assertSent(function (PutSupplierInvoice $sent): bool {
            $body = $sent->body()->all();

            return [
                'create' => [['label' => 'Demo label', 'currency_amount' => '120.00', 'currency_tax' => '20.00', 'vat_rate' => 'FR_200']],
                'update' => [['id' => 1, 'label' => 'Updated label']],
                'delete' => [['id' => 2]],
            ] === $body['invoice_lines'];
        });
    }
}
