<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\Category;

final readonly class CategoryDto
{
    public function __construct(
        public int $id,
        public string $label,
        public ?CategoryDirection $direction,
        public string $createdAt,
        public string $updatedAt,
        public ?int $categoryGroupId,
        public ?string $analyticalCode,
        public ?string $archivedAt = null,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $label = $body['label'] ?? null;
        $direction = $body['direction'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;
        $categoryGroup = $body['category_group'] ?? null;
        $analyticalCode = $body['analytical_code'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_string($label)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "label" textuel.');
        }

        if (null !== $direction && !is_string($direction)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "direction" textuelle ou nulle.');
        }

        if (!is_string($createdAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "created_at" textuel.');
        }

        if (!is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "updated_at" textuel.');
        }

        if (null !== $categoryGroup && !is_array($categoryGroup)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "category_group" objet ou absent.');
        }

        if (null !== $analyticalCode && !is_string($analyticalCode)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "analytical_code" textuel ou nul.');
        }

        $archivedAt = $body['archived_at'] ?? null;

        if (null !== $archivedAt && !is_string($archivedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "archived_at" textuel ou nul.');
        }

        $categoryGroupId = null;

        if (null !== $categoryGroup) {
            $rawGroupId = $categoryGroup['id'] ?? null;

            if (!is_int($rawGroupId)) {
                throw new \InvalidArgumentException('Le "category_group" de la réponse Pennylane doit contenir un "id" entier.');
            }

            $categoryGroupId = $rawGroupId;
        }

        return new self(
            id: $id,
            label: $label,
            direction: null !== $direction ? CategoryDirection::from($direction) : null,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            categoryGroupId: $categoryGroupId,
            analyticalCode: $analyticalCode,
            archivedAt: $archivedAt,
        );
    }
}
