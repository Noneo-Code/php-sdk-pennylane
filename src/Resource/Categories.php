<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Filter\Sort;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\Categories\GetCategories;
use Noneo\Sdk\Pennylane\Requests\Categories\GetCategory;
use Noneo\Sdk\Pennylane\Requests\Categories\PostCategories;
use Noneo\Sdk\Pennylane\Requests\Categories\UpdateCategory;
use Saloon\Http\BaseResource;

final class Categories extends BaseResource
{
    public function list(
        ?string $cursor = null,
        ?int $limit = null,
        ?FilterSet $filters = null,
        ?Sort $sort = null,
    ): CursorPage {
        $response = $this->connector->send(new GetCategories($cursor, $limit, $filters, $sort));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginate(?FilterSet $filters = null, ?Sort $sort = null): \Generator
    {
        $paginator = new CursorPaginator(
            $this->connector,
            new GetCategories(filters: $filters, sort: $sort),
            filters: $filters,
            sort: $sort,
        );

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CategoryDto
    {
        return CategoryDto::fromArray($this->connector->send(new GetCategory($id))->json());
    }

    public function create(
        string $label,
        int $categoryGroupId,
        ?CategoryDirection $direction = null,
        ?string $analyticalCode = null,
    ): CategoryDto {
        $request = new PostCategories($label, $categoryGroupId, $direction, $analyticalCode);

        return CategoryDto::fromArray($this->connector->send($request)->json());
    }

    public function update(
        int $id,
        ?string $label = null,
        ?CategoryDirection $direction = null,
        ?string $analyticalCode = null,
    ): CategoryDto {
        $request = new UpdateCategory($id, $label, $direction, $analyticalCode);

        return CategoryDto::fromArray($this->connector->send($request)->json());
    }

    /** @return mixed[] */
    private static function asArray(mixed $item): array
    {
        if (!is_array($item)) {
            throw new \InvalidArgumentException('Un item de la réponse Pennylane doit être un objet.');
        }

        return $item;
    }
}
