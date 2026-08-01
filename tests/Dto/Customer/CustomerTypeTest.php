<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Customer;

use Noneo\Sdk\Pennylane\Dto\Customer\CustomerType;
use PHPUnit\Framework\TestCase;

final class CustomerTypeTest extends TestCase
{
    public function testItExposesTheTwoCustomerTypes(): void
    {
        self::assertSame('company', CustomerType::Company->value);
        self::assertSame('individual', CustomerType::Individual->value);
    }
}
