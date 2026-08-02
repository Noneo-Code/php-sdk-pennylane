<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

final readonly class Page
{
    /** @param mixed[] $items */
    public function __construct(
        public array $items,
        public int $totalPages,
        public int $currentPage,
        public int $totalItems,
        public int $perPage,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $items = $body['items'] ?? null;
        $totalPages = $body['total_pages'] ?? null;
        $currentPage = $body['current_page'] ?? null;
        $totalItems = $body['total_items'] ?? null;
        $perPage = $body['per_page'] ?? null;

        if (!is_array($items)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "items".');
        }

        if (!is_int($totalPages) || !is_int($currentPage) || !is_int($totalItems) || !is_int($perPage)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "total_pages", "current_page", "total_items" et "per_page" sous forme d\'entiers.');
        }

        return new self($items, $totalPages, $currentPage, $totalItems, $perPage);
    }
}
