<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\GetIndividualCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;

final class GetIndividualCustomerTest extends TestCase
{
    public function testSendsAGetRequestToTheIndividualCustomerEndpoint(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new GetIndividualCustomer(42));

        $mockClient->assertSent(function (Request $sent): bool {
            return Method::GET === $sent->getMethod()
                && '/individual_customers/42' === $sent->resolveEndpoint();
        });
    }
}
