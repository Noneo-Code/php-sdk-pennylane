<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryGroupDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CategoryGroupsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedCategoryGroupPayload(): array
    {
        return [
            'id' => 989,
            'label' => 'Types de dépenses / revenus',
            'categories' => ['url' => 'https://app.pennylane.com/api/external/v2/category_groups/42/categories'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    /** @return array<string, mixed> */
    private function documentedCategoryInGroupPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'analytical_code' => 'CODE123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListReturnsACursorPageOfCategoryGroupDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryGroupPayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->categoryGroups()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertFalse($page->hasMore);
        self::assertInstanceOf(CategoryGroupDto::class, $page->items[0]);
        self::assertSame(989, $page->items[0]->id);
    }

    public function testGetReturnsACategoryGroupDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedCategoryGroupPayload())]));

        $group = $pennylane->categoryGroups()->get(989);

        self::assertInstanceOf(CategoryGroupDto::class, $group);
        self::assertSame(989, $group->id);
    }

    public function testCategoriesReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryInGroupPayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $page = $pennylane->categoryGroups()->categories(42);

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
        self::assertNull($page->items[0]->categoryGroupId);
    }

    public function testPaginateYieldsCategoryGroupDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryGroupPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $groups = iterator_to_array($pennylane->categoryGroups()->paginate());

        self::assertCount(1, $groups);
        self::assertInstanceOf(CategoryGroupDto::class, $groups[0]);
    }

    public function testPaginateCategoriesYieldsCategoryDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryInGroupPayload()],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $categories = iterator_to_array($pennylane->categoryGroups()->paginateCategories(42));

        self::assertCount(1, $categories);
        self::assertInstanceOf(CategoryDto::class, $categories[0]);
    }
}
