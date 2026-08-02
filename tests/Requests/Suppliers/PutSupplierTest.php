<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Suppliers;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Http\Clear;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Suppliers\PutSupplier;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutSupplierTest extends TestCase
{
    public function testSendsAPutRequestToTheSupplierEndpoint(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplier(id: 42, name: 'Renamed Pennylane'));

        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/suppliers/42' === $sent->resolveEndpoint()
                && ['name' => 'Renamed Pennylane'] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalAddressFields(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplier(
            id: 42,
            postalAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
        ));

        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return ['postal_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR']] === $sent->body()->all();
        });
    }

    public function testOmitsFieldsThatWereNotProvided(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplier(id: 42, name: 'ACME', externalReference: null));

        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return ['name' => 'ACME'] === $sent->body()->all();
        });
    }

    public function testSendsAnExplicitNullForAClearedField(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->withMockClient($mockClient);

        $pennylane->send(new PutSupplier(id: 42, externalReference: Clear::value()));

        $mockClient->assertSent(function (PutSupplier $sent): bool {
            return ['external_reference' => null] === $sent->body()->all();
        });
    }
}
