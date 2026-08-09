<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\CustomerInvoice;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\ImputationDates;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\InvoiceLineDto;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class InvoiceLineDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getCustomerInvoiceInvoiceLines', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 444,
            'label' => 'Demo label',
            'unit' => 'piece',
            'quantity' => '12',
            'amount' => '50.4',
            'currency_amount' => '50.4',
            'description' => 'Lorem ipsum dolor sit amet',
            'product' => ['id' => 3049, 'url' => 'https://app.pennylane.com/api/external/v2/products/42'],
            'vat_rate' => 'FR_200',
            'currency_amount_before_tax' => '30',
            'currency_tax' => '10',
            'tax' => '10',
            'raw_currency_unit_price' => '5',
            'discount' => ['type' => 'absolute', 'value' => '25'],
            'section_rank' => 1,
            'imputation_dates' => ['start_date' => '2020-06-30', 'end_date' => '2021-06-30'],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
            'substance' => 'goods',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $line = InvoiceLineDto::fromArray(self::documentedPayload());

        self::assertSame(444, $line->id);
        self::assertSame('Demo label', $line->label);
        self::assertSame('piece', $line->unit);
        self::assertSame('12', $line->quantity);
        self::assertSame(3049, $line->productId);
        self::assertSame('https://app.pennylane.com/api/external/v2/products/42', $line->productUrl);
        self::assertSame(VatRate::Fr200, $line->vatRate);
        self::assertSame(1, $line->sectionRank);
        self::assertInstanceOf(ImputationDates::class, $line->imputationDates);
        self::assertSame(Substance::Goods, $line->substance);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['product'] = null;
        $payload['section_rank'] = null;
        $payload['imputation_dates'] = null;
        $payload['substance'] = null;

        $line = InvoiceLineDto::fromArray($payload);

        self::assertNull($line->productId);
        self::assertNull($line->productUrl);
        self::assertNull($line->sectionRank);
        self::assertNull($line->imputationDates);
        self::assertNull($line->substance);
    }
}
