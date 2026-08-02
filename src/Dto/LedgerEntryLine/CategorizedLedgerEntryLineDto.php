<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;

final readonly class CategorizedLedgerEntryLineDto
{
    use DecodesPennylanePayloads;

    /** @param CategoryDto[] $categories */
    public function __construct(
        public int $id,
        public string $label,
        public array $categories,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $categories = $body['categories'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('La "ledger_entry_line" de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('La "ledger_entry_line" de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_array($categories)) {
            throw new \InvalidArgumentException('La "ledger_entry_line" de la réponse Pennylane doit contenir un tableau "categories".');
        }

        return new self(
            id: $id,
            label: $label,
            categories: array_map(
                static fn (mixed $item): CategoryDto => CategoryDto::fromArray(self::asArray($item)),
                $categories,
            ),
        );
    }
}
