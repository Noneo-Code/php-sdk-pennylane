<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoicePaymentStatus;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\PaymentDto;
use PHPUnit\Framework\TestCase;

final class PaymentDtoTest extends TestCase
{
    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $payment = PaymentDto::fromArray([
            'id' => 444,
            'label' => 'Demo label',
            'currency' => 'EUR',
            'currency_amount' => '230.32',
            'status' => 'paid_out',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertSame(444, $payment->id);
        self::assertSame('Demo label', $payment->label);
        self::assertSame(Currency::Eur, $payment->currency);
        self::assertSame('230.32', $payment->currencyAmount);
        self::assertSame(CustomerInvoicePaymentStatus::PaidOut, $payment->status);
    }

    public function testItHandlesANullCurrency(): void
    {
        $payment = PaymentDto::fromArray([
            'id' => 444,
            'label' => 'Demo label',
            'currency' => null,
            'currency_amount' => '230.32',
            'status' => 'draft',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ]);

        self::assertNull($payment->currency);
    }
}
