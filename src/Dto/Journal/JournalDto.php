<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Journal;

final readonly class JournalDto
{
    public function __construct(
        public string $code,
        public int $id,
        public string $label,
        public string $type,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $code = $body['code'] ?? null;
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $type = $body['type'] ?? null;

        if (!is_string($code)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "code" textuel.');
        }

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_string($type)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "type" textuel.');
        }

        return new self(code: $code, id: $id, label: $label, type: $type);
    }
}
