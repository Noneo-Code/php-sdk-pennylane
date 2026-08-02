<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Dms;

use Noneo\Sdk\Pennylane\Dto\Dms\DmsFolderDto;
use PHPUnit\Framework\TestCase;

final class DmsFolderDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $dto = DmsFolderDto::fromArray([
            'id' => 124,
            'name' => 'folder_name',
            'path' => '/path/to/folder',
            'parent_folder' => ['id' => 234],
            'files' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1/dms/files?filter=%5B%5D'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(124, $dto->id);
        self::assertSame('folder_name', $dto->name);
        self::assertSame('/path/to/folder', $dto->path);
        self::assertSame(234, $dto->parentFolderId);
        self::assertSame('https://app.pennylane.com/api/external/firm/v1/companies/1/dms/files?filter=%5B%5D', $dto->filesUrl);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItAllowsAFolderAtRootLevel(): void
    {
        $dto = DmsFolderDto::fromArray([
            'id' => 124,
            'name' => 'folder_name',
            'path' => '/folder_name',
            'parent_folder' => null,
            'files' => ['url' => 'https://app.pennylane.com/api/external/firm/v1/companies/1/dms/files'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($dto->parentFolderId);
    }
}
