<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImportedInvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\UpdateImportedInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateImportedCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateImportedCustomerInvoiceTest extends TestCase
{
    public function testSendsAPutRequestWithNoFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateImportedCustomerInvoice(42));

        $mockClient->assertSent(function (UpdateImportedCustomerInvoice $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/customer_invoices/42/update_imported' === $sent->resolveEndpoint()
                && [] === $sent->body()->all();
        });
    }

    public function testSendsTheProvidedFieldsIncludingInvoiceLinePatches(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateImportedCustomerInvoice(
            id: 42,
            invoiceNumber: 'F20230001',
            invoiceLines: new ImportedInvoiceLinesPatch(
                update: [new UpdateImportedInvoiceLine(id: 444, currencyAmount: '50.4')],
                delete: [7],
            ),
        ));

        $mockClient->assertSent(function (UpdateImportedCustomerInvoice $sent): bool {
            return [
                'invoice_number' => 'F20230001',
                'invoice_lines' => [
                    'update' => [['id' => 444, 'currency_amount' => '50.4']],
                    'delete' => [['id' => 7]],
                ],
            ] === $sent->body()->all();
        });
    }
}
