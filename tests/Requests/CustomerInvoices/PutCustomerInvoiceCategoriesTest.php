<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\CustomerInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\CustomerInvoices\PutCustomerInvoiceCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutCustomerInvoiceCategoriesTest extends TestCase
{
    public function testSendsAPutRequestWithTheCategoryAllocations(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutCustomerInvoiceCategories(42, [
            new CategoryAllocation(59, '0.5'),
            new CategoryAllocation(33, '0.5'),
        ]));

        $mockClient->assertSent(function (PutCustomerInvoiceCategories $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/customer_invoices/42/categories' === $sent->resolveEndpoint()
                && [
                    ['id' => 59, 'weight' => '0.5'],
                    ['id' => 33, 'weight' => '0.5'],
                ] === $sent->body()->all();
        });
    }
}
