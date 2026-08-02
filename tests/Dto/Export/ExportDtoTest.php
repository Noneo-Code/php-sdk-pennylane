<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\ExportDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportStatus;
use PHPUnit\Framework\TestCase;

final class ExportDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedExportCreationPayload(): void
    {
        $dto = ExportDto::fromArray([
            'id' => 124,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(124, $dto->id);
        self::assertSame(ExportStatus::Pending, $dto->status);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }
}
