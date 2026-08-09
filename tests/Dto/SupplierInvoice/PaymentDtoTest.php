<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\CustomerInvoicePaymentStatus;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\PaymentDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class PaymentDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getSupplierInvoicePayments', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 444,
            'label' => 'Demo label',
            'currency' => 'EUR',
            'currency_amount' => '230.32',
            'status' => 'initiated',
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFromArrayBuildsAPaymentDtoFromTheDocumentedPayload(): void
    {
        $payment = PaymentDto::fromArray($this->documentedPayload());

        self::assertSame(444, $payment->id);
        self::assertSame('Demo label', $payment->label);
        self::assertSame(Currency::Eur, $payment->currency);
        self::assertSame('230.32', $payment->currencyAmount);
        self::assertSame(CustomerInvoicePaymentStatus::Initiated, $payment->status);
    }

    public function testFromArrayAllowsCurrencyToBeNull(): void
    {
        $payload = $this->documentedPayload();
        $payload['currency'] = null;

        $payment = PaymentDto::fromArray($payload);

        self::assertNull($payment->currency);
    }
}
