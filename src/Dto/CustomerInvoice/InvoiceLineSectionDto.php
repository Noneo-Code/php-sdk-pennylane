<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class InvoiceLineSectionDto
{
    public function __construct(
        public int $id,
        public ?string $title,
        public ?string $description,
        public int $rank,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $rank = $body['rank'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('La section de facture de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_int($rank)) {
            throw new \InvalidArgumentException('La section de facture de la réponse Pennylane doit contenir un "rank" entier.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('La section de facture de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            title: is_string($body['title'] ?? null) ? $body['title'] : null,
            description: is_string($body['description'] ?? null) ? $body['description'] : null,
            rank: $rank,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
