<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoice;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetSupplierInvoiceTest extends TestCase
{
    public function testSendsAGetRequestToTheSupplierInvoiceEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 123])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetSupplierInvoice(123));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod() && '/supplier_invoices/123' === $sent->resolveEndpoint();
        });
    }
}
