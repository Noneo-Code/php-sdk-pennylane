<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PurchaseRequest;

final readonly class DeliveryAddressDto
{
    public function __construct(
        public ?string $address,
        public ?string $postalCode,
        public ?string $city,
        public ?string $countryAlpha2,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $address = $body['address'] ?? null;
        $postalCode = $body['postal_code'] ?? null;
        $city = $body['city'] ?? null;
        $countryAlpha2 = $body['country_alpha2'] ?? null;

        if (null !== $address && !is_string($address)) {
            throw new \InvalidArgumentException('L\'"address" de la réponse Pennylane doit être textuelle ou nulle.');
        }

        if (null !== $postalCode && !is_string($postalCode)) {
            throw new \InvalidArgumentException('Le "postal_code" de la réponse Pennylane doit être textuel ou nul.');
        }

        if (null !== $city && !is_string($city)) {
            throw new \InvalidArgumentException('La "city" de la réponse Pennylane doit être textuelle ou nulle.');
        }

        if (null !== $countryAlpha2 && !is_string($countryAlpha2)) {
            throw new \InvalidArgumentException('Le "country_alpha2" de la réponse Pennylane doit être textuel ou nul.');
        }

        return new self($address, $postalCode, $city, $countryAlpha2);
    }
}
