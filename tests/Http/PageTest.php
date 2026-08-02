<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Http;

use Noneo\Sdk\Pennylane\Http\Page;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testItIsBuiltFromThePennylanePageEnvelope(): void
    {
        $page = Page::fromArray([
            'items' => [['id' => 1], ['id' => 2]],
            'total_pages' => 5,
            'current_page' => 1,
            'total_items' => 12,
            'per_page' => 40,
        ]);

        self::assertSame([['id' => 1], ['id' => 2]], $page->items);
        self::assertSame(5, $page->totalPages);
        self::assertSame(1, $page->currentPage);
        self::assertSame(12, $page->totalItems);
        self::assertSame(40, $page->perPage);
    }

    public function testItRejectsAResponseBodyMissingTheItemsEnvelope(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Page::fromArray(['total_pages' => 1, 'current_page' => 1, 'total_items' => 0, 'per_page' => 20]);
    }
}
