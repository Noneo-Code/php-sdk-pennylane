<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Resource;

use Noneo\Sdk\Pennylane\Concerns\DecodesPennylanePayloads;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryGroupDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Http\CursorPaginator;
use Noneo\Sdk\Pennylane\Requests\CategoryGroups\GetCategoryGroup;
use Noneo\Sdk\Pennylane\Requests\CategoryGroups\GetCategoryGroupCategories;
use Noneo\Sdk\Pennylane\Requests\CategoryGroups\GetCategoryGroups;
use Saloon\Http\BaseResource;

final class CategoryGroups extends BaseResource
{
    use DecodesPennylanePayloads;

    public function list(?string $cursor = null, ?int $limit = null): CursorPage
    {
        $response = $this->connector->send(new GetCategoryGroups($cursor, $limit));
        $rawPage = CursorPage::fromArray($response->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryGroupDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryGroupDto> */
    public function paginate(?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCategoryGroups(), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CategoryGroupDto::fromArray(self::asArray($item));
        }
    }

    public function get(int $id): CategoryGroupDto
    {
        return CategoryGroupDto::fromArray($this->connector->send(new GetCategoryGroup($id))->json());
    }

    public function categories(int $categoryGroupId, ?string $cursor = null, ?int $limit = null): CursorPage
    {
        $request = new GetCategoryGroupCategories($categoryGroupId, $cursor, $limit);
        $rawPage = CursorPage::fromArray($this->connector->send($request)->json());

        $items = [];
        foreach ($rawPage->items as $item) {
            $items[] = CategoryDto::fromArray(self::asArray($item));
        }

        return new CursorPage($items, $rawPage->hasMore, $rawPage->nextCursor);
    }

    /** @return \Generator<int, CategoryDto> */
    public function paginateCategories(int $categoryGroupId, ?int $perPage = null): \Generator
    {
        $paginator = new CursorPaginator($this->connector, new GetCategoryGroupCategories($categoryGroupId), perPage: $perPage);

        foreach ($paginator->items() as $item) {
            yield CategoryDto::fromArray(self::asArray($item));
        }
    }
}
