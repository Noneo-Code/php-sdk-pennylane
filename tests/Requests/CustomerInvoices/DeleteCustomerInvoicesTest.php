<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\DeleteCustomerInvoices;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class DeleteCustomerInvoicesTest extends TestCase
{
    public function testSendsADeleteRequestToTheCustomerInvoiceEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new DeleteCustomerInvoices(1234));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::DELETE === $sent->getMethod()
                && '/customer_invoices/1234' === $sent->resolveEndpoint();
        });
    }
}
