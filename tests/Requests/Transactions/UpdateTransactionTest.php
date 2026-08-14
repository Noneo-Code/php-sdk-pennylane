<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Transactions;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Transactions\UpdateTransaction;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class UpdateTransactionTest extends TestCase
{
    public function testSendsAPutRequestWithTheCustomerId(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateTransaction(42, customerId: 7));

        $mockClient->assertSent(function (UpdateTransaction $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/transactions/42' === $sent->resolveEndpoint()
                && ['customer_id' => 7] === $sent->body()->all();
        });
    }

    public function testSendsAPutRequestWithTheSupplierId(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new UpdateTransaction(42, supplierId: 9));

        $mockClient->assertSent(function (UpdateTransaction $sent): bool {
            return ['supplier_id' => 9] === $sent->body()->all();
        });
    }

    public function testThrowsWhenNeitherCustomerNorSupplierIsProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UpdateTransaction(42);
    }

    public function testThrowsWhenBothCustomerAndSupplierAreProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new UpdateTransaction(42, customerId: 7, supplierId: 9);
    }
}
