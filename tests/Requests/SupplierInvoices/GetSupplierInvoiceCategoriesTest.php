<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\GetSupplierInvoiceCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetSupplierInvoiceCategoriesTest extends TestCase
{
    public function testSendsAGetRequestToTheCategoriesEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetSupplierInvoiceCategories(42, cursor: 'abc', limit: 10));

        $mockClient->assertSent(function (Request $sent, Response $response): bool {
            return Method::GET === $sent->getMethod()
                && '/supplier_invoices/42/categories' === $sent->resolveEndpoint()
                && ['cursor' => 'abc', 'limit' => 10] === $response->getPendingRequest()->query()->all();
        });
    }
}
