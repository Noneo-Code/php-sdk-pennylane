<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Filter;

use Noneo\Sdk\Pennylane\Filter\FilterOperator;
use PHPUnit\Framework\TestCase;

final class FilterOperatorTest extends TestCase
{
    public function testItExposesTheNineOperatorsSupportedByThePennylaneApi(): void
    {
        self::assertSame('eq', FilterOperator::Eq->value);
        self::assertSame('not_eq', FilterOperator::NotEq->value);
        self::assertSame('lt', FilterOperator::Lt->value);
        self::assertSame('lteq', FilterOperator::Lteq->value);
        self::assertSame('gt', FilterOperator::Gt->value);
        self::assertSame('gteq', FilterOperator::Gteq->value);
        self::assertSame('in', FilterOperator::In->value);
        self::assertSame('not_in', FilterOperator::NotIn->value);
        self::assertSame('start_with', FilterOperator::StartWith->value);
    }
}
