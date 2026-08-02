<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\FiscalYear;

final readonly class FiscalYearDto
{
    public function __construct(
        public int $id,
        public string $start,
        public string $finish,
        public FiscalYearStatus $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $start = $body['start'] ?? null;
        $finish = $body['finish'] ?? null;
        $status = $body['status'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($start)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "start" textuel.');
        }

        if (!is_string($finish)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "finish" textuel.');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        return new self(
            id: $id,
            start: $start,
            finish: $finish,
            status: FiscalYearStatus::from($status),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
