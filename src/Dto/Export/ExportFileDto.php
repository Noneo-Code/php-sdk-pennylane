<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Export;

final readonly class ExportFileDto
{
    public function __construct(
        public int $id,
        public ?string $fileUrl,
        public ExportStatus $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $fileUrl = $body['file_url'] ?? null;
        $status = $body['status'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($fileUrl) && null !== $fileUrl) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "file_url" textuel ou nul.');
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
            fileUrl: $fileUrl,
            status: ExportStatus::from($status),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
