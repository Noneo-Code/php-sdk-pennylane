<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    public function testItBuildsFromTheDocumentedAddressPayload(): void
    {
        $address = Address::fromArray([
            'address' => '8 rue de la paix',
            'postal_code' => '75002',
            'city' => 'Paris',
            'country_alpha2' => 'FR',
        ]);

        self::assertSame('8 rue de la paix', $address->address);
        self::assertSame('75002', $address->postalCode);
        self::assertSame('Paris', $address->city);
        self::assertSame('FR', $address->countryAlpha2);
    }

    public function testItConvertsBackToTheApiShapeForRequestBodies(): void
    {
        $address = new Address('8 rue de la paix', '75002', 'Paris', 'FR');

        self::assertSame([
            'address' => '8 rue de la paix',
            'postal_code' => '75002',
            'city' => 'Paris',
            'country_alpha2' => 'FR',
        ], $address->toArray());
    }
}
