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

    /**
     * Un filtre sur un montant se compare à une chaîne décimale, comme les
     * montants du corps : le float est refusé dès la construction, là où la
     * trace pointe encore la ligne d'appel.
     */
    public function testItRefusesAFloatValueOnConstruction(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount');

        new Filter('amount', FilterOperator::Gteq, 1.5);
    }
}
