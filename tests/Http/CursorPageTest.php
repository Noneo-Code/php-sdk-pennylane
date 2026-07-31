<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Http\CursorPage;
use PHPUnit\Framework\TestCase;

final class CursorPageTest extends TestCase
{
    public function testItIsBuiltFromThePennylaneEnvelope(): void
    {
        $page = CursorPage::fromArray([
            'items' => [['id' => 1], ['id' => 2]],
            'has_more' => true,
            'next_cursor' => 'eyJpZCI6MTAwfQ==',
        ]);

        self::assertSame([['id' => 1], ['id' => 2]], $page->items);
        self::assertTrue($page->hasMore);
        self::assertSame('eyJpZCI6MTAwfQ==', $page->nextCursor);
    }

    public function testTheNextCursorIsNullOnTheLastPage(): void
    {
        $page = CursorPage::fromArray([
            'items' => [],
            'has_more' => false,
            'next_cursor' => null,
        ]);

        self::assertFalse($page->hasMore);
        self::assertNull($page->nextCursor);
    }

    public function testItRejectsAResponseBodyMissingTheItemsEnvelope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CursorPage::fromArray(['has_more' => false, 'next_cursor' => null]);
    }
}
