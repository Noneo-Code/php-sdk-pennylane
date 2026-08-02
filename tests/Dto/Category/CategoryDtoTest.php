<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Category;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use Noneo\Sdk\Pennylane\Dto\Category\CategoryDto;
use PHPUnit\Framework\TestCase;

final class CategoryDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedGetCategoryPayload(): void
    {
        $dto = CategoryDto::fromArray([
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'category_group' => ['id' => 348],
            'analytical_code' => 'CODE123',
        ]);

        self::assertSame(42, $dto->id);
        self::assertSame('Alimentaire', $dto->label);
        self::assertNull($dto->direction);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
        self::assertSame(348, $dto->categoryGroupId);
        self::assertSame('CODE123', $dto->analyticalCode);
    }

    public function testItParsesATreasuryDirection(): void
    {
        $dto = CategoryDto::fromArray([
            'id' => 1,
            'label' => 'Loyers',
            'direction' => 'cash_out',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'category_group' => ['id' => 23],
            'analytical_code' => null,
        ]);

        self::assertSame(CategoryDirection::CashOut, $dto->direction);
        self::assertNull($dto->analyticalCode);
    }

    public function testItAllowsAMissingCategoryGroupAsReturnedByTheGroupCategoriesListing(): void
    {
        $dto = CategoryDto::fromArray([
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'analytical_code' => 'CODE123',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($dto->categoryGroupId);
    }

    public function testItParsesTheArchivedAtReturnedByTheFirmApi(): void
    {
        $dto = CategoryDto::fromArray([
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'archived_at' => '2023-09-01T00:00:00.000000Z',
            'category_group' => ['id' => 348],
            'analytical_code' => 'CODE123',
        ]);

        self::assertSame('2023-09-01T00:00:00.000000Z', $dto->archivedAt);
    }

    public function testTheArchivedAtIsNullWhenAbsentAsReturnedByTheCompanyApi(): void
    {
        $dto = CategoryDto::fromArray([
            'id' => 42,
            'label' => 'Alimentaire',
            'direction' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'category_group' => ['id' => 348],
            'analytical_code' => 'CODE123',
        ]);

        self::assertNull($dto->archivedAt);
    }
}
