<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Category;

final readonly class CategoryGroupDto
{
    public function __construct(
        public int $id,
        public string $label,
        public string $categoriesUrl,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $categories = $body['categories'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (!is_array($categories)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "categories".');
        }

        $categoriesUrl = $categories['url'] ?? null;

        if (!is_string($categoriesUrl)) {
            throw new \InvalidArgumentException('Le "categories" de la réponse Pennylane doit contenir une "url" textuelle.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        return new self(
            id: $id,
            label: $label,
            categoriesUrl: $categoriesUrl,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
