<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\PurchaseRequest;

use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\DeliveryAddressDto;
use PHPUnit\Framework\TestCase;

final class DeliveryAddressDtoTest extends TestCase
{
    public function testFromArrayBuildsADeliveryAddressDtoFromTheDocumentedPayload(): void
    {
        $address = DeliveryAddressDto::fromArray([
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

    public function testFromArrayAllowsAllFieldsToBeNull(): void
    {
        $address = DeliveryAddressDto::fromArray([
            'address' => null,
            'postal_code' => null,
            'city' => null,
            'country_alpha2' => null,
        ]);

        self::assertNull($address->address);
        self::assertNull($address->postalCode);
        self::assertNull($address->city);
        self::assertNull($address->countryAlpha2);
    }
}
