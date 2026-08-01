<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentStatusUpdate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\UpdateSupplierInvoicePaymentStatus;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateSupplierInvoicePaymentStatusTest extends TestCase
{
    public function testSendsAPutRequestWithThePaymentStatus(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new UpdateSupplierInvoicePaymentStatus(123, PaymentStatusUpdate::Paid));

        $mockClient->assertSent(function (UpdateSupplierInvoicePaymentStatus $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/supplier_invoices/123/payment_status' === $sent->resolveEndpoint()
                && ['payment_status' => 'paid'] === $sent->body()->all();
        });
    }
}
