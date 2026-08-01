<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\SupplierInvoices;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\SupplierInvoices\PutSupplierInvoiceCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSupplierInvoiceCategoriesTest extends TestCase
{
    public function testSendsAPutRequestWithTheCategoryAllocations(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplierInvoiceCategories(42, [
            new CategoryAllocation(59, '0.5'),
            new CategoryAllocation(33, '0.5'),
        ]));

        $mockClient->assertSent(function (PutSupplierInvoiceCategories $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/supplier_invoices/42/categories' === $sent->resolveEndpoint()
                && [
                    ['id' => 59, 'weight' => '0.5'],
                    ['id' => 33, 'weight' => '0.5'],
                ] === $sent->body()->all();
        });
    }
}
