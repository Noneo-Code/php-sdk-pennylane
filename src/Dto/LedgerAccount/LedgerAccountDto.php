<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerAccount;

final readonly class LedgerAccountDto
{
    public function __construct(
        public int $id,
        public string $number,
        public string $label,
        public VatRate $vatRate,
        public CountryAlpha2 $countryAlpha2,
        public bool $enabled,
        public ?string $type,
        public bool $letterable,
        public ?string $createdAt,
        public ?string $updatedAt,
        public ?string $archivedAt = null,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $number = $body['number'] ?? null;
        $label = $body['label'] ?? null;
        $vatRate = $body['vat_rate'] ?? null;
        $countryAlpha2 = $body['country_alpha2'] ?? null;
        $enabled = $body['enabled'] ?? null;
        $type = $body['type'] ?? null;
        $letterable = $body['letterable'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($number)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "number" textuel.');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_string($vatRate)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "vat_rate" textuel.');
        }

        if (!is_string($countryAlpha2)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "country_alpha2" textuel.');
        }

        if (!is_bool($enabled)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "enabled".');
        }

        if (null !== $type && !is_string($type)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "type" textuel ou nul.');
        }

        if (!is_bool($letterable)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "letterable".');
        }

        if (null !== $createdAt && !is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel ou nul.');
        }

        if (null !== $updatedAt && !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel ou nul.');
        }

        $archivedAt = $body['archived_at'] ?? null;

        if (null !== $archivedAt && !is_string($archivedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "archived_at" textuel ou nul.');
        }

        return new self(
            id: $id,
            number: $number,
            label: $label,
            vatRate: VatRate::from($vatRate),
            countryAlpha2: CountryAlpha2::from($countryAlpha2),
            enabled: $enabled,
            type: $type,
            letterable: $letterable,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            archivedAt: $archivedAt,
        );
    }
}
