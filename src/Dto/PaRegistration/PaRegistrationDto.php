<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PaRegistration;

final readonly class PaRegistrationDto
{
    public function __construct(
        public int $id,
        public ?string $siret,
        public string $siren,
        public PaRegistrationStatus $status,
        public ExchangeDirection $exchangeDirection,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $siret = $body['siret'] ?? null;
        $siren = $body['siren'] ?? null;
        $status = $body['status'] ?? null;
        $exchangeDirection = $body['exchange_direction'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (null !== $siret && !is_string($siret)) {
            throw new \InvalidArgumentException('Le "siret" de la réponse Pennylane doit être textuel ou nul.');
        }

        if (!is_string($siren)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "siren" textuel.');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_string($exchangeDirection)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "exchange_direction" textuelle.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            siret: $siret,
            siren: $siren,
            status: PaRegistrationStatus::from($status),
            exchangeDirection: ExchangeDirection::from($exchangeDirection),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
