<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\MarkAsPaidCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class MarkAsPaidCustomerInvoiceTest extends TestCase
{
    public function testSendsAPutRequestToTheMarkAsPaidEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new MarkAsPaidCustomerInvoice(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/customer_invoices/42/mark_as_paid' === $sent->resolveEndpoint();
        });
    }
}
