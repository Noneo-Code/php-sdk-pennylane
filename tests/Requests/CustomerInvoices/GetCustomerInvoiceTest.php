<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\GetCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetCustomerInvoiceTest extends TestCase
{
    public function testSendsAGetRequestToTheCustomerInvoiceEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new GetCustomerInvoice(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/customer_invoices/42' === $sent->resolveEndpoint();
        });
    }
}
