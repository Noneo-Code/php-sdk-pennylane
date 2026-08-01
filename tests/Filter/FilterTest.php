<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Filter;

use Noneo\Sdk\Pennylane\Filter\Filter;
use Noneo\Sdk\Pennylane\Filter\FilterOperator;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    public function testItSerializesToTheShapeExpectedByThePennylaneApi(): void
    {
        $filter = new Filter('id', FilterOperator::Eq, 1);

        self::assertSame(
            ['field' => 'id', 'operator' => 'eq', 'value' => 1],
            $filter->jsonSerialize(),
        );
    }
}
