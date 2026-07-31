<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Filter;

use Noneo\Sdk\Pennylane\Filter\Sort;
use PHPUnit\Framework\TestCase;

final class SortTest extends TestCase
{
    public function testAscendingSortSerializesToTheBareFieldName(): void
    {
        self::assertSame('date', (string) Sort::asc('date'));
    }

    public function testDescendingSortSerializesToTheFieldNamePrefixedWithAMinus(): void
    {
        self::assertSame('-date', (string) Sort::desc('date'));
    }
}
