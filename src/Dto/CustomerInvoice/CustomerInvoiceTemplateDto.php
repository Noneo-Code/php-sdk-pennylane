<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class CustomerInvoiceTemplateDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le modèle de facture de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($name)) {
            throw new \InvalidArgumentException('Le modèle de facture de la réponse Pennylane doit contenir un "name" textuel.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le modèle de facture de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self($id, $name, $createdAt, $updatedAt);
    }
}
