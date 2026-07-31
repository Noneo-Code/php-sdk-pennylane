<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Http;

final readonly class CursorPage
{
    /** @param mixed[] $items */
    public function __construct(
        public array $items,
        public bool $hasMore,
        public ?string $nextCursor,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $items = $body['items'] ?? null;
        $hasMore = $body['has_more'] ?? null;
        $nextCursor = $body['next_cursor'] ?? null;

        if (!is_array($items)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un tableau "items".');
        }

        if (!is_bool($hasMore)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un booléen "has_more".');
        }

        if (!is_string($nextCursor) && null !== $nextCursor) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "next_cursor" textuel ou nul.');
        }

        return new self($items, $hasMore, $nextCursor);
    }
}
