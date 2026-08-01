<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\EInvoiceStatusTransition;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\SupplierEInvoiceReason;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoiceEInvoiceStatus;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSupplierInvoiceEInvoiceStatusTest extends TestCase
{
    public function testSendsADisputeWithItsReason(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 12345])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplierInvoiceEInvoiceStatus(12345, EInvoiceStatusTransition::Disputed, SupplierEInvoiceReason::IncorrectVatRate));

        $mockClient->assertSent(function (PutSupplierInvoiceEInvoiceStatus $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/supplier_invoices/12345/e_invoice_status' === $sent->resolveEndpoint()
                && ['status' => 'disputed', 'reason' => 'incorrect_vat_rate'] === $sent->body()->all();
        });
    }

    public function testSendsAnUndisputeWithoutAReason(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 12345])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplierInvoiceEInvoiceStatus(12345, EInvoiceStatusTransition::Approved));

        $mockClient->assertSent(function (PutSupplierInvoiceEInvoiceStatus $sent): bool {
            return ['status' => 'approved'] === $sent->body()->all();
        });
    }
}
