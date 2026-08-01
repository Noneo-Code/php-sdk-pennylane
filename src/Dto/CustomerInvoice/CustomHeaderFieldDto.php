<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class CustomHeaderFieldDto
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $value,
        public int $rank,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $title = $body['title'] ?? null;
        $rank = $body['rank'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le champ d\'en-tête de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($title)) {
            throw new \InvalidArgumentException('Le champ d\'en-tête de la réponse Pennylane doit contenir un "title" textuel.');
        }

        if (!is_int($rank)) {
            throw new \InvalidArgumentException('Le champ d\'en-tête de la réponse Pennylane doit contenir un "rank" entier.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le champ d\'en-tête de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            title: $title,
            value: is_string($body['value'] ?? null) ? $body['value'] : null,
            rank: $rank,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
