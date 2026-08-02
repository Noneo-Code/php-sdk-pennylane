<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Changelog;

final readonly class ChangelogEntryDto
{
    public function __construct(
        public int $id,
        public ChangelogOperation $operation,
        public string $processedAt,
        public string $updatedAt,
        public string $createdAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $operation = $body['operation'] ?? null;
        $processedAt = $body['processed_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $createdAt = $body['created_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($operation)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "operation" textuelle.');
        }

        if (!is_string($processedAt) || !is_string($updatedAt) || !is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "processed_at", "updated_at" et "created_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            operation: ChangelogOperation::from($operation),
            processedAt: $processedAt,
            updatedAt: $updatedAt,
            createdAt: $createdAt,
        );
    }
}
