<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Changelog;

final readonly class LedgerEntryLineCategoryChangelogEntryDto
{
    public function __construct(
        public int $id,
        public ChangelogOperation $operation,
        public ?int $ledgerEntryLineId,
        public ?int $ledgerEntryId,
        public ?string $ledgerEntryType,
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
        $ledgerEntryLine = $body['ledger_entry_line'] ?? null;
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

        if (null !== $ledgerEntryLine && !is_array($ledgerEntryLine)) {
            throw new \InvalidArgumentException('Le "ledger_entry_line" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (null !== $ledgerEntry && !is_array($ledgerEntry)) {
            throw new \InvalidArgumentException('Le "ledger_entry" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_string($processedAt) || !is_string($updatedAt) || !is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "processed_at", "updated_at" et "created_at" sous forme de texte.');
        }

        return new self(
            id: $id,
            operation: ChangelogOperation::from($operation),
            ledgerEntryLineId: is_array($ledgerEntryLine) && is_int($ledgerEntryLine['id'] ?? null) ? $ledgerEntryLine['id'] : null,
            ledgerEntryId: is_array($ledgerEntry) && is_int($ledgerEntry['id'] ?? null) ? $ledgerEntry['id'] : null,
            ledgerEntryType: is_array($ledgerEntry) && is_string($ledgerEntry['type'] ?? null) ? $ledgerEntry['type'] : null,
            processedAt: $processedAt,
            updatedAt: $updatedAt,
            createdAt: $createdAt,
        );
    }
}
