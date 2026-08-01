<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\PutIndividualCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutIndividualCustomerTest extends TestCase
{
    public function testSendsAPutRequestToTheIndividualCustomerEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutIndividualCustomer(id: 42, lastName: 'Smith'));

        $mockClient->assertSent(function (PutIndividualCustomer $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/individual_customers/42' === $sent->resolveEndpoint()
                && ['last_name' => 'Smith'] === $sent->body()->all();
        });
    }
}
