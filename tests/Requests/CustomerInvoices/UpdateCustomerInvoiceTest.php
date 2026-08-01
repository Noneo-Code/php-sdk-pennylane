<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLinesPatch;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\NewProductInvoiceLine;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\UpdateInvoiceLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\UpdateCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateCustomerInvoiceTest extends TestCase
{
    public function testSendsAPutRequestWithNoFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateCustomerInvoice(42));

        $mockClient->assertSent(function (UpdateCustomerInvoice $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/customer_invoices/42' === $sent->resolveEndpoint()
                && [] === $sent->body()->all();
        });
    }

    public function testSendsTheProvidedFieldsIncludingInvoiceLinePatches(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateCustomerInvoice(
            id: 42,
            label: 'Renamed invoice',
            invoiceLines: new InvoiceLinesPatch(
                create: [new NewProductInvoiceLine(productId: 3049, quantity: 2)],
                update: [new UpdateInvoiceLine(id: 444, label: 'Updated label')],
                delete: [12, 13],
            ),
        ));

        $mockClient->assertSent(function (UpdateCustomerInvoice $sent): bool {
            return [
                'invoice_lines' => [
                    'create' => [['product_id' => 3049, 'quantity' => 2]],
                    'update' => [['id' => 444, 'label' => 'Updated label']],
                    'delete' => [['id' => 12], ['id' => 13]],
                ],
                'label' => 'Renamed invoice',
            ] === $sent->body()->all();
        });
    }
}
