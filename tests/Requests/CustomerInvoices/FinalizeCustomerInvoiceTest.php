<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\FinalizeCustomerInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class FinalizeCustomerInvoiceTest extends TestCase
{
    public function testSendsAPutRequestToTheFinalizeEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new FinalizeCustomerInvoice(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/customer_invoices/42/finalize' === $sent->resolveEndpoint();
        });
    }
}
