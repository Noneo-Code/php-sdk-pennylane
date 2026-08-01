<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class AppendixDto
{
    public function __construct(
        public int $id,
        public string $url,
        public string $filename,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $url = $body['url'] ?? null;
        $filename = $body['filename'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('L\'annexe de la réponse Pennylane doit contenir un "id" entier.');
        }

        if (!is_string($url) || !is_string($filename)) {
            throw new \InvalidArgumentException('L\'annexe de la réponse Pennylane doit contenir une "url" et un "filename" textuels.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('L\'annexe de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self($id, $url, $filename, $createdAt, $updatedAt);
    }
}
