<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\FileAttachment;

use Noneo\Sdk\Pennylane\Dto\FileAttachment\FileAttachmentDto;
use PHPUnit\Framework\TestCase;

final class FileAttachmentDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    public static function documentedPayload(): array
    {
        return [
            'id' => 1,
            'url' => 'http://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/Invoice42.pdf',
            'filename' => 'Invoice42.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $fileAttachment = FileAttachmentDto::fromArray(self::documentedPayload());

        self::assertSame(1, $fileAttachment->id);
        self::assertSame('http://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/Invoice42.pdf', $fileAttachment->url);
        self::assertSame('Invoice42.pdf', $fileAttachment->filename);
        self::assertSame('2023-08-30T10:08:08.146343Z', $fileAttachment->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $fileAttachment->updatedAt);
    }

    public function testItRejectsAMissingId(): void
    {
        $payload = self::documentedPayload();
        unset($payload['id']);

        $this->expectException(\InvalidArgumentException::class);

        FileAttachmentDto::fromArray($payload);
    }
}
