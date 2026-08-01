<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Customers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Customers\PutCompanyCustomer;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutCompanyCustomerTest extends TestCase
{
    public function testSendsAPutRequestToTheCompanyCustomerEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutCompanyCustomer(id: 42, name: 'My Renamed Company'));

        $mockClient->assertSent(function (PutCompanyCustomer $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/company_customers/42' === $sent->resolveEndpoint()
                && ['name' => 'My Renamed Company'] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalAddressFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutCompanyCustomer(
            id: 42,
            billingAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        ));

        $mockClient->assertSent(function (PutCompanyCustomer $sent): bool {
            return ['billing_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR']] === $sent->body()->all();
        });
    }
}
