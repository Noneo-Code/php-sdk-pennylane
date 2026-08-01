<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Supplier;

use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierDueDateRule;
use PHPUnit\Framework\TestCase;

final class SupplierDueDateRuleTest extends TestCase
{
    public function testItExposesAllDocumentedDueDateRules(): void
    {
        self::assertSame('days', SupplierDueDateRule::Days->value);
        self::assertSame('days_or_end_of_month', SupplierDueDateRule::DaysOrEndOfMonth->value);
    }
}
