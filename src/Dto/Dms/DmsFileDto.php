<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Dms;

final readonly class DmsFileDto
{
    public function __construct(
        public int $id,
        public string $name,
        public string $path,
        public ?int $parentFolderId,
        public string $url,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $name = $body['name'] ?? null;
        $path = $body['path'] ?? null;
        $parentFolder = $body['parent_folder'] ?? null;
        $url = $body['url'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($name) || !is_string($path) || !is_string($url)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "name", "path" et "url" sous forme de texte.');
        }

        if (null !== $parentFolder && (!is_array($parentFolder) || !is_int($parentFolder['id'] ?? null))) {
            throw new \InvalidArgumentException('Le "parent_folder" de la réponse Pennylane doit être un objet avec un "id" entier, ou nul.');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at".');
        }

        return new self(
            id: $id,
            name: $name,
            path: $path,
            parentFolderId: is_array($parentFolder) ? $parentFolder['id'] : null,
            url: $url,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
