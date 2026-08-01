<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Customer;

final readonly class Address
{
    public function __construct(
        public string $address,
        public string $postalCode,
        public string $city,
        public string $countryAlpha2,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $address = $body['address'] ?? null;
        $postalCode = $body['postal_code'] ?? null;
        $city = $body['city'] ?? null;
        $countryAlpha2 = $body['country_alpha2'] ?? null;

        if (!is_string($address)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "address" textuelle.');
        }

        if (!is_string($postalCode)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "postal_code" textuel.');
        }

        if (!is_string($city)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "city" textuelle.');
        }

        if (!is_string($countryAlpha2)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "country_alpha2" textuel.');
        }

        return new self($address, $postalCode, $city, $countryAlpha2);
    }

    /** @return array{address: string, postal_code: string, city: string, country_alpha2: string} */
    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country_alpha2' => $this->countryAlpha2,
        ];
    }
}
