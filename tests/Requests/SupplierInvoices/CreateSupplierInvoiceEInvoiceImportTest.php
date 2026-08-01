<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\EInvoiceLineOptions;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceImportOptions;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\CreateSupplierInvoiceEInvoiceImport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CreateSupplierInvoiceEInvoiceImportTest extends TestCase
{
    public function testSendsAMultipartPostRequestWithTheFile(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateSupplierInvoiceEInvoiceImport('%PDF-1.4 fake content', 'invoice.pdf'));

        $mockClient->assertSent(function (CreateSupplierInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();

            return Method::POST === $sent->getMethod()
                && '/supplier_invoices/e_invoices/imports' === $sent->resolveEndpoint()
                && 1 === count($parts)
                && 'file' === $parts[0]->name
                && 'invoice.pdf' === $parts[0]->filename
                && '%PDF-1.4 fake content' === $parts[0]->value;
        });
    }

    public function testSendsTheInvoiceOptionsAsAJsonMultipartPart(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42'], 201)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new CreateSupplierInvoiceEInvoiceImport(
            'content',
            'invoice.pdf',
            new EInvoiceImportOptions(
                supplierId: 12,
                invoiceLines: [new EInvoiceLineOptions(eInvoiceLineId: 'BT-126-1', ledgerAccountId: 123)],
            ),
        ));

        $mockClient->assertSent(function (CreateSupplierInvoiceEInvoiceImport $sent): bool {
            $parts = $sent->body()->all();
            $optionsPart = null;
            foreach ($parts as $part) {
                if ('invoice_options' === $part->name) {
                    $optionsPart = $part;
                }
            }

            return null !== $optionsPart
                && '{"supplier_id":12,"invoice_lines":[{"e_invoice_line_id":"BT-126-1","ledger_account_id":123}]}' === $optionsPart->value;
        });
    }
}
