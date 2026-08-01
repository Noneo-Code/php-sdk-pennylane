<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\DiscountType;
use PHPUnit\Framework\TestCase;

final class DiscountTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $discount = Discount::fromArray(['type' => 'relative', 'value' => '25']);

        self::assertSame(DiscountType::Relative, $discount->type);
        self::assertSame('25', $discount->value);
    }

    public function testToArrayRoundTrips(): void
    {
        $discount = new Discount(DiscountType::Absolute, '100.00');

        self::assertSame(['type' => 'absolute', 'value' => '100.00'], $discount->toArray());
    }
}
