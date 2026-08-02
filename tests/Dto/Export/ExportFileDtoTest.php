<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Export;

use Noneo\Sdk\Pennylane\Dto\Export\ExportFileDto;
use Noneo\Sdk\Pennylane\Dto\Export\ExportStatus;
use PHPUnit\Framework\TestCase;

final class ExportFileDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedReadyExportPayload(): void
    {
        $dto = ExportFileDto::fromArray([
            'id' => 124,
            'file_url' => 'https://app.pennylane.com/my_export.xlsx',
            'status' => 'ready',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(124, $dto->id);
        self::assertSame('https://app.pennylane.com/my_export.xlsx', $dto->fileUrl);
        self::assertSame(ExportStatus::Ready, $dto->status);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItAcceptsANullFileUrlWhileStillPending(): void
    {
        $dto = ExportFileDto::fromArray([
            'id' => 124,
            'file_url' => null,
            'status' => 'pending',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($dto->fileUrl);
    }
}
