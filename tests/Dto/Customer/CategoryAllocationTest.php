<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\CategoryAllocation;
use PHPUnit\Framework\TestCase;

final class CategoryAllocationTest extends TestCase
{
    public function testItConvertsToTheApiShapeForRequestBodies(): void
    {
        $allocation = new CategoryAllocation(426, '0.6575');

        self::assertSame(['id' => 426, 'weight' => '0.6575'], $allocation->toArray());
    }
}
