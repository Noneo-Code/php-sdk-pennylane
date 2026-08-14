<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use Noneo\Sdk\Pennylane\Filter\FilterSet;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Categories\GetCategories;
use Noneo\Sdk\Pennylane\Requests\Categories\PostCategories;
use Noneo\Sdk\Pennylane\Requests\Categories\UpdateCategory;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

final class CategoriesTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedCategoryPayload(): array
    {
        return [
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'category_group' => ['id' => 348],
            'analytical_code' => 'CODE123',
        ];
    }

    public function testListReturnsACursorPageOfCategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
        ]));

        $page = $pennylane->categories()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertTrue($page->hasMore);
        self::assertSame('cursor-2', $page->nextCursor);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(CategoryDto::class, $page->items[0]);
        self::assertSame(42, $page->items[0]->id);
    }

    public function testListForwardsFilterAndSort(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([
            MockResponse::make(['items' => [], 'has_more' => false, 'next_cursor' => null]),
        ]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->categories()->list(filters: FilterSet::make()->eq('category_group_id', 42));

        $mockClient->assertSent(function (GetCategories $sent, Response $response): bool {
            return '[{"field":"category_group_id","operator":"eq","value":42}]' === ($response->getPendingRequest()->query()->all()['filter'] ?? null);
        });
    }

    public function testGetReturnsACategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([MockResponse::make($this->documentedCategoryPayload())]));

        $category = $pennylane->categories()->get(42);

        self::assertInstanceOf(CategoryDto::class, $category);
        self::assertSame(42, $category->id);
        self::assertSame('Alimentaire', $category->label);
    }

    public function testCreateReturnsACategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedCategoryPayload(), 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $category = $pennylane->categories()->create(label: 'Alimentaire', categoryGroupId: 348);

        self::assertInstanceOf(CategoryDto::class, $category);
        $mockClient->assertSent(function (PostCategories $sent): bool {
            return [
                'label' => 'Alimentaire',
                'category_group_id' => 348,
            ] === $sent->body()->all();
        });
    }

    public function testUpdateReturnsACategoryDto(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make($this->documentedCategoryPayload())]);
        $pennylane->connector()->withMockClient($mockClient);

        $category = $pennylane->categories()->update(42, direction: CategoryDirection::CashOut);

        self::assertInstanceOf(CategoryDto::class, $category);
        $mockClient->assertSent(function (UpdateCategory $sent): bool {
            return ['direction' => 'cash_out'] === $sent->body()->all();
        });
    }

    public function testPaginateYieldsCategoryDtoAcrossPages(): void
    {
        $pennylane = new Pennylane('a-token');
        $pennylane->connector()->withMockClient(new MockClient([
            MockResponse::make([
                'items' => [$this->documentedCategoryPayload()],
                'has_more' => true,
                'next_cursor' => 'cursor-2',
            ]),
            MockResponse::make([
                'items' => [['id' => 43, 'label' => 'Loyers', 'direction' => null, 'created_at' => '2023-08-30T10:08:08.146343Z', 'updated_at' => '2023-08-30T10:08:08.146343Z', 'category_group' => ['id' => 348], 'analytical_code' => null]],
                'has_more' => false,
                'next_cursor' => null,
            ]),
        ]));

        $categories = iterator_to_array($pennylane->categories()->paginate());

        self::assertCount(2, $categories);
        self::assertSame(42, $categories[0]->id);
        self::assertSame(43, $categories[1]->id);
    }
}
