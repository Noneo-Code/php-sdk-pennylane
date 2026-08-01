<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\AppendixDto;
use PHPUnit\Framework\TestCase;

final class AppendixDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $appendix = AppendixDto::fromArray([
            'id' => 1,
            'url' => 'https://www.pennylane.com/rails/active_storage/blobs/redirect/eyJfc..==--26d6e7391dd728fae2cde59bd3fbe4dd11e20a95/Invoice42.pdf',
            'filename' => 'Invoice42.pdf',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(1, $appendix->id);
        self::assertSame('Invoice42.pdf', $appendix->filename);
    }
}
