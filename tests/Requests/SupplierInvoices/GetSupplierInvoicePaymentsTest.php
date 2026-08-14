<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoicePayments;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetSupplierInvoicePaymentsTest extends TestCase
{
    public function testSendsAGetRequestToThePaymentsEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetSupplierInvoicePayments(1234, cursor: 'abc', limit: 10, sort: Sort::desc('id')));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/supplier_invoices/1234/payments' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10, 'sort' => '-id'] === $response->getPendingRequest()->query()->all();
        });
    }
}
