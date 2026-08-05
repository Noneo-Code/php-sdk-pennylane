<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Quote;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\Quote\QuoteInvoiceLineDto;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class QuoteInvoiceLineDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getQuoteInvoiceLines', pointer: 'items')]
    public static function documentedPayload(): array
    {
        return [
            'id' => 444,
            'label' => 'Consulting fees',
            'unit' => 'piece',
            'quantity' => '2.0',
            'amount' => '66.67',
            'currency_amount' => '66.67',
            'description' => 'Consulting services for August',
            'product' => ['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/products/42'],
            'vat_rate' => 'FR_200',
            'currency_amount_before_tax' => '55.56',
            'currency_tax' => '11.11',
            'tax' => '11.11',
            'raw_currency_unit_price' => '33.333334',
            'discount' => ['type' => 'absolute', 'value' => '10.00'],
            'section_rank' => 1,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $line = QuoteInvoiceLineDto::fromArray(self::documentedPayload());

        self::assertSame(444, $line->id);
        self::assertSame('Consulting fees', $line->label);
        self::assertSame('piece', $line->unit);
        self::assertSame('2.0', $line->quantity);
        self::assertSame(42, $line->productId);
        self::assertSame('https://app.pennylane.com/api/external/v2/products/42', $line->productUrl);
        self::assertSame(VatRate::Fr200, $line->vatRate);
        self::assertSame(1, $line->sectionRank);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['unit'] = null;
        $payload['product'] = null;
        $payload['section_rank'] = null;

        $line = QuoteInvoiceLineDto::fromArray($payload);

        self::assertNull($line->unit);
        self::assertNull($line->productId);
        self::assertNull($line->productUrl);
        self::assertNull($line->sectionRank);
    }
}
