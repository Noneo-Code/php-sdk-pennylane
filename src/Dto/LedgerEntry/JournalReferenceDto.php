<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\LedgerEntry;

final readonly class JournalReferenceDto
{
    public function __construct(
        public int $id,
        public string $url,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $url = $body['url'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le "journal" de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($url)) {
            throw new \InvalidArgumentException('Le "journal" de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        return new self($id, $url);
    }
}
