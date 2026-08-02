<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Changelog;

use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogEntryDto;
use Noneo\Sdk\Pennylane\Dto\Changelog\ChangelogOperation;
use PHPUnit\Framework\TestCase;

final class ChangelogEntryDtoTest extends TestCase
{
    public function testBuildsFromTheDocumentedPayload(): void
    {
        $dto = ChangelogEntryDto::fromArray([
            'id' => 123,
            'operation' => 'insert',
            'processed_at' => '2025-06-25T11:54:18.589480Z',
            'updated_at' => '2025-06-25T11:54:18.589480Z',
            'created_at' => '2025-06-25T11:54:18.589480Z',
        ]);

        self::assertSame(123, $dto->id);
        self::assertSame(ChangelogOperation::Insert, $dto->operation);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->processedAt);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->updatedAt);
        self::assertSame('2025-06-25T11:54:18.589480Z', $dto->createdAt);
    }
}
