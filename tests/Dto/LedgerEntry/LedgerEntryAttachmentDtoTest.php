<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntry;

use Noneo\Sdk\Pennylane\Dto\LedgerEntry\LedgerEntryAttachmentDto;
use PHPUnit\Framework\TestCase;

final class LedgerEntryAttachmentDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedAttachmentPayload(): void
    {
        $dto = LedgerEntryAttachmentDto::fromArray([
            'filename' => 'filename.pdf',
            'url' => 'http://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/filename.pdf',
        ]);

        self::assertSame('filename.pdf', $dto->filename);
        self::assertSame('http://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/filename.pdf', $dto->url);
    }
}
