<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\Product;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\Product\ProductDto;
use PHPUnit\Framework\TestCase;

final class ProductDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    public static function documentedPayload(): array
    {
        return [
            'id' => 1,
            'label' => 'Product 1',
            'description' => 'This is product 1',
            'external_reference' => '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
            'price_before_tax' => '12.5',
            'vat_rate' => 'FR_200',
            'price' => '13.6',
            'unit' => 'piece',
            'currency' => 'EUR',
            'reference' => 'REF-123',
            'substance' => 'goods',
            'ledger_account' => ['id' => 42],
            'archived_at' => null,
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testItBuildsFromTheDocumentedPayload(): void
    {
        $product = ProductDto::fromArray(self::documentedPayload());

        self::assertSame(1, $product->id);
        self::assertSame('Product 1', $product->label);
        self::assertSame('This is product 1', $product->description);
        self::assertSame('0e67fc3c-c632-4feb-ad34-e18ed5fbf66a', $product->externalReference);
        self::assertSame('12.5', $product->priceBeforeTax);
        self::assertSame(VatRate::Fr200, $product->vatRate);
        self::assertSame('13.6', $product->price);
        self::assertSame('piece', $product->unit);
        self::assertSame(Currency::Eur, $product->currency);
        self::assertSame('REF-123', $product->reference);
        self::assertSame(Substance::Goods, $product->substance);
        self::assertSame(42, $product->ledgerAccountId);
        self::assertNull($product->archivedAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $product->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $product->updatedAt);
    }

    public function testItHandlesNullableFields(): void
    {
        $payload = self::documentedPayload();
        $payload['reference'] = null;
        $payload['substance'] = null;
        $payload['ledger_account'] = null;
        $payload['archived_at'] = '2023-09-30T10:08:08.146343Z';

        $product = ProductDto::fromArray($payload);

        self::assertNull($product->reference);
        self::assertNull($product->substance);
        self::assertNull($product->ledgerAccountId);
        self::assertSame('2023-09-30T10:08:08.146343Z', $product->archivedAt);
    }
}
