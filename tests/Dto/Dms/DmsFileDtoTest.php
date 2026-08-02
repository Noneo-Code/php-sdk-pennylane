<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Dms;

use Noneo\Sdk\Pennylane\Dto\Dms\DmsFileDto;
use PHPUnit\Framework\TestCase;

final class DmsFileDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $dto = DmsFileDto::fromArray([
            'id' => 124,
            'name' => 'file.pdf',
            'path' => '/path/to/file.pdf',
            'parent_folder' => ['id' => 234],
            'url' => 'https://example.com/path/to/file.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(124, $dto->id);
        self::assertSame('file.pdf', $dto->name);
        self::assertSame('/path/to/file.pdf', $dto->path);
        self::assertSame(234, $dto->parentFolderId);
        self::assertSame('https://example.com/path/to/file.pdf', $dto->url);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $dto->updatedAt);
    }

    public function testItAllowsAFileAtRootLevel(): void
    {
        $dto = DmsFileDto::fromArray([
            'id' => 124,
            'name' => 'file.pdf',
            'path' => '/file.pdf',
            'parent_folder' => null,
            'url' => 'https://example.com/file.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($dto->parentFolderId);
    }
}
