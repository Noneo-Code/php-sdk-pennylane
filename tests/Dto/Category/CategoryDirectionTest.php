<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Category;

use Noneo\Sdk\Pennylane\Dto\Category\CategoryDirection;
use PHPUnit\Framework\TestCase;

final class CategoryDirectionTest extends TestCase
{
    public function testItExposesTheTwoTreasuryDirections(): void
    {
        self::assertSame('cash_in', CategoryDirection::CashIn->value);
        self::assertSame('cash_out', CategoryDirection::CashOut->value);
    }
}
