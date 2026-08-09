<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Filter;

use Noneo\Sdk\Pennylane\Filter\FilterSet;
use PHPUnit\Framework\TestCase;

final class FilterSetTest extends TestCase
{
    public function testItProducesTheExactJsonShapeDocumentedByThePennylaneApi(): void
    {
        $filters = FilterSet::make()->eq('id', 1);

        self::assertSame(
            '[{"field":"id","operator":"eq","value":1}]',
            $filters->toJson(),
        );
    }

    public function testItChainsMultipleOperatorsInDeclarationOrder(): void
    {
        $filters = FilterSet::make()
            ->gteq('date', '2026-01-01')
            ->in('customer_id', [12, 34])
            ->startWith('label', 'ACME');

        self::assertSame(
            '[{"field":"date","operator":"gteq","value":"2026-01-01"},'
            .'{"field":"customer_id","operator":"in","value":[12,34]},'
            .'{"field":"label","operator":"start_with","value":"ACME"}]',
            $filters->toJson(),
        );
    }

    public function testAnEmptyFilterSetSerializesToAnEmptyJsonArray(): void
    {
        self::assertSame('[]', FilterSet::make()->toJson());
    }

    public function testItRefusesAFloatComparisonValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount');

        FilterSet::make()->gteq('amount', 1.5);
    }

    public function testItRefusesAFloatHiddenInAnInList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount[1]');

        FilterSet::make()->in('amount', ['12.00', 2.5]);
    }

    public function testItStillAcceptsDecimalStringsAndIntegers(): void
    {
        $filters = FilterSet::make()->gteq('amount', '120.50')->eq('customer_id', 12);

        self::assertSame(
            '[{"field":"amount","operator":"gteq","value":"120.50"},'
            .'{"field":"customer_id","operator":"eq","value":12}]',
            $filters->toJson(),
        );
    }
}
