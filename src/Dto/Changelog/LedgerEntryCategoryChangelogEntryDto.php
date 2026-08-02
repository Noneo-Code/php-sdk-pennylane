<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Changelog;

final readonly class LedgerEntryCategoryChangelogEntryDto
{
    public function __construct(
        public int $id,
        public ChangelogOperation $operation,
        public int $ledgerEntryId,
        public string $ledgerEntryType,
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
        $ledgerEntry = $body['ledger_entry'] ?? null;
        $processedAt = $body['processed_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $createdAt = $body['created_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($operation)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "operation" textuelle.');
        }

        if (!is_array($ledgerEntry) || !is_int($ledgerEntry['id'] ?? null) || !is_string($ledgerEntry['type'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_entry" avec un "id" entier et un "type" textuel.');
        }

        if (!is_string($processedAt) || !is_string($updatedAt) || !is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "processed_at", "updated_at" et "created_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            operation: ChangelogOperation::from($operation),
            ledgerEntryId: $ledgerEntry['id'],
            ledgerEntryType: $ledgerEntry['type'],
            processedAt: $processedAt,
            updatedAt: $updatedAt,
            createdAt: $createdAt,
        );
    }
}
