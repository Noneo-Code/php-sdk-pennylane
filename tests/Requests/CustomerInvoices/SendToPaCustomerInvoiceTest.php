<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\SendToPaCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class SendToPaCustomerInvoiceTest extends TestCase
{
    public function testSendsAPostRequestToTheSendToPaEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([], 204)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new SendToPaCustomerInvoice(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/customer_invoices/42/send_to_pa' === $sent->resolveEndpoint();
        });
    }
}
