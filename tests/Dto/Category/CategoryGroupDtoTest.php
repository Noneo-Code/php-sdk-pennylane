<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Category;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryGroupDto;
use PHPUnit\Framework\TestCase;

final class CategoryGroupDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedGetCategoryGroupPayload(): void
    {
        $dto = CategoryGroupDto::fromArray([
            'id' => 989,
            'label' => 'Types de dépenses / revenus',
            'categories' => ['url' => 'https://app.pennylane.com/api/external/v2/category_groups/42/categories'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(989, $dto->id);
        self::assertSame('Types de dépenses / revenus', $dto->label);
        self::assertSame('https://app.pennylane.com/api/external/v2/category_groups/42/categories', $dto->categoriesUrl);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }
}
