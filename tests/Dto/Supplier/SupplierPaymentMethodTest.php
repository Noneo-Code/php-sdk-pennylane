<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Supplier;

use Noneo\Sdk\Pennylane\Dto\Supplier\SupplierPaymentMethod;
use PHPUnit\Framework\TestCase;

final class SupplierPaymentMethodTest extends TestCase
{
    public function testItExposesAllDocumentedPaymentMethods(): void
    {
        self::assertSame('automatic_transfer', SupplierPaymentMethod::AutomaticTransfer->value);
        self::assertSame('manual_transfer', SupplierPaymentMethod::ManualTransfer->value);
        self::assertSame('automatic_debiting', SupplierPaymentMethod::AutomaticDebiting->value);
        self::assertSame('bill_of_exchange', SupplierPaymentMethod::BillOfExchange->value);
        self::assertSame('check', SupplierPaymentMethod::Check->value);
        self::assertSame('cash', SupplierPaymentMethod::Cash->value);
        self::assertSame('card', SupplierPaymentMethod::Card->value);
        self::assertSame('other', SupplierPaymentMethod::Other->value);
    }
}
