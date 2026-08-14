<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Suppliers;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplierCategories;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSupplierCategoriesTest extends TestCase
{
    public function testSendsAPutRequestWithTheCategoryAllocations(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make([])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutSupplierCategories(42, [
            new CategoryAllocation(59, '0.5'),
            new CategoryAllocation(33, '0.5'),
        ]));

        $mockClient->assertSent(function (PutSupplierCategories $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/suppliers/42/categories' === $sent->resolveEndpoint()
                && [
                    ['id' => 59, 'weight' => '0.5'],
                    ['id' => 33, 'weight' => '0.5'],
                ] === $sent->body()->all();
        });
    }
}
