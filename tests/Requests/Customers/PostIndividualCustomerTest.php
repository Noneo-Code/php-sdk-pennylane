<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\PostIndividualCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostIndividualCustomerTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostIndividualCustomer(
            firstName: 'John',
            lastName: 'Doe',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        ));

        $mockClient->assertSent(function (PostIndividualCustomer $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/individual_customers' === $sent->resolveEndpoint()
                && [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalLedgerAccountNumber(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostIndividualCustomer(
            firstName: 'John',
            lastName: 'Doe',
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            ledgerAccountNumber: '411100344',
        ));

        $mockClient->assertSent(function (PostIndividualCustomer $sent): bool {
            return ['number' => '411100344'] === ($sent->body()->all()['ledger_account'] ?? null);
        });
    }
}
