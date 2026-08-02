<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\LedgerEntryLine;

use Noneo\Sdk\Pennylane\Dto\LedgerEntryLine\CategoryAllocation;
use PHPUnit\Framework\TestCase;

final class CategoryAllocationTest extends TestCase
{
    public function testItConvertsToTheApiShapeForRequestBodies(): void
    {
        $allocation = new CategoryAllocation(394, '0.6575');

        self::assertSame(['id' => 394, 'weight' => '0.6575'], $allocation->toArray());
    }
}
