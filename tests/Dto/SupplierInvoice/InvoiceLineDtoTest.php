<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\SupplierInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\SupplierInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class InvoiceLineDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getSupplierInvoiceLines', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 444,
            'label' => 'Demo label',
            'amount' => '50.4',
            'currency_amount' => '50.4',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor',
            'vat_rate' => 'FR_200',
            'currency_amount_before_tax' => '30',
            'currency_tax' => '10',
            'tax' => '10',
            'imputation_dates' => ['start_date' => '2020-06-30', 'end_date' => '2021-06-30'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFromArrayBuildsAnInvoiceLineDtoFromTheDocumentedPayload(): void
    {
        $line = InvoiceLineDto::fromArray($this->documentedPayload());

        self::assertSame(444, $line->id);
        self::assertSame('Demo label', $line->label);
        self::assertSame('50.4', $line->amount);
        self::assertSame(VatRate::Fr200, $line->vatRate);
        self::assertNotNull($line->imputationDates);
        self::assertSame('2020-06-30', $line->imputationDates->startDate);
    }

    public function testFromArrayAllowsImputationDatesToBeNull(): void
    {
        $payload = $this->documentedPayload();
        $payload['imputation_dates'] = null;

        $line = InvoiceLineDto::fromArray($payload);

        self::assertNull($line->imputationDates);
    }
}
