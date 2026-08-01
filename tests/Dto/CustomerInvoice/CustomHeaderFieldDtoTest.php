<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomHeaderFieldDto;
use PHPUnit\Framework\TestCase;

final class CustomHeaderFieldDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $field = CustomHeaderFieldDto::fromArray([
            'id' => 444,
            'title' => 'customer_reference',
            'value' => 'REF-12345',
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(444, $field->id);
        self::assertSame('customer_reference', $field->title);
        self::assertSame('REF-12345', $field->value);
        self::assertSame(1, $field->rank);
    }

    public function testItHandlesANullValue(): void
    {
        $field = CustomHeaderFieldDto::fromArray([
            'id' => 444,
            'title' => 'customer_reference',
            'value' => null,
            'rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($field->value);
    }
}
