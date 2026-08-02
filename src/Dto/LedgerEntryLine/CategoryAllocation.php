<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntryLine;

final readonly class CategoryAllocation
{
    public function __construct(
        public int $categoryId,
        public string $weight,
    ) {
    }

    /** @return array{id: int, weight: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->categoryId,
            'weight' => $this->weight,
        ];
    }
}
