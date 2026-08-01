<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineSectionDto;
use PHPUnit\Framework\TestCase;

final class InvoiceLineSectionDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $section = InvoiceLineSectionDto::fromArray([
            'id' => 444,
            'title' => 'Section #1',
            'description' => 'Lorem ipsum dolor sit amet',
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(444, $section->id);
        self::assertSame('Section #1', $section->title);
        self::assertSame('Lorem ipsum dolor sit amet', $section->description);
        self::assertSame(1, $section->rank);
        self::assertSame('2023-08-30T10:08:08.146343Z', $section->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $section->updatedAt);
    }

    public function testItHandlesNullableTitleAndDescription(): void
    {
        $section = InvoiceLineSectionDto::fromArray([
            'id' => 444,
            'title' => null,
            'description' => null,
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($section->title);
        self::assertNull($section->description);
    }
}
