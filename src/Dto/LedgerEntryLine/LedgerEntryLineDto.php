<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\JournalReferenceDto;
use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerAccountReferenceDto;

final readonly class LedgerEntryLineDto
{
    /**
     * @param CategoryDto[] $categories
     * @param int[]         $letteredLedgerEntryLineIds
     */
    public function __construct(
        public int $id,
        public string $debit,
        public string $credit,
        public string $label,
        public array $categories,
        public LedgerAccountReferenceDto $ledgerAccount,
        public JournalReferenceDto $journal,
        public string $date,
        public int $ledgerEntryId,
        public array $letteredLedgerEntryLineIds,
        public string $letteredLedgerEntryLinesUrl,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $debit = $body['debit'] ?? null;
        $credit = $body['credit'] ?? null;
        $label = $body['label'] ?? null;
        $categories = $body['categories'] ?? null;
        $ledgerAccount = $body['ledger_account'] ?? null;
        $journal = $body['journal'] ?? null;
        $date = $body['date'] ?? null;
        $ledgerEntry = $body['ledger_entry'] ?? null;
        $letteredLedgerEntryLines = $body['lettered_ledger_entry_lines'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($debit)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "debit" textuel.');
        }

        if (!is_string($credit)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "credit" textuel.');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_array($categories)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "categories".');
        }

        if (!is_array($ledgerAccount)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_account" objet.');
        }

        if (!is_array($journal)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "journal" objet.');
        }

        if (!is_string($date)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "date" textuelle.');
        }

        if (!is_array($ledgerEntry) || !is_int($ledgerEntry['id'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "ledger_entry" objet avec un "id" entier.');
        }

        if (!is_array($letteredLedgerEntryLines) || !is_array($letteredLedgerEntryLines['ids'] ?? null) || !is_string($letteredLedgerEntryLines['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "lettered_ledger_entry_lines" objet avec des "ids" et une "url".');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        return new self(
            id: $id,
            debit: $debit,
            credit: $credit,
            label: $label,
            categories: array_map(
                static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)),
                $categories,
            ),
            ledgerAccount: LedgerAccountReferenceDto::fromArray($ledgerAccount),
            journal: JournalReferenceDto::fromArray($journal),
            date: $date,
            ledgerEntryId: $ledgerEntry['id'],
            letteredLedgerEntryLineIds: array_map(
                static fn (mixed $item): int => is_int($item) ? $item : throw new \InvalidArgumentException('Les "ids" de "lettered_ledger_entry_lines" doivent être des entiers.'),
                $letteredLedgerEntryLines['ids'],
            ),
            letteredLedgerEntryLinesUrl: $letteredLedgerEntryLines['url'],
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /** @return mixed[] */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
