<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\PaymentConditions;
use PHPUnit\Framework\TestCase;

final class PaymentConditionsTest extends TestCase
{
    public function testItExposesAllDocumentedPaymentConditions(): void
    {
        self::assertSame('upon_receipt', PaymentConditions::UponReceipt->value);
        self::assertSame('custom', PaymentConditions::Custom->value);
        self::assertSame('7_days', PaymentConditions::SevenDays->value);
        self::assertSame('15_days', PaymentConditions::FifteenDays->value);
        self::assertSame('30_days', PaymentConditions::ThirtyDays->value);
        self::assertSame('30_days_end_of_month', PaymentConditions::ThirtyDaysEndOfMonth->value);
        self::assertSame('45_days', PaymentConditions::FortyFiveDays->value);
        self::assertSame('45_days_end_of_month', PaymentConditions::FortyFiveDaysEndOfMonth->value);
        self::assertSame('60_days', PaymentConditions::SixtyDays->value);
    }
}
