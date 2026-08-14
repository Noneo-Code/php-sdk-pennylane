<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Products;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Substance;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Products\PostProducts;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PostProductsTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostProducts(
            label: 'Product 1',
            priceBeforeTax: '100.00',
            vatRate: VatRate::Fr200,
        ));

        $mockClient->assertSent(function (PostProducts $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/products' === $sent->resolveEndpoint()
                && [
                    'label' => 'Product 1',
                    'price_before_tax' => '100.00',
                    'vat_rate' => 'FR_200',
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PostProducts(
            label: 'Product 1',
            priceBeforeTax: '100.00',
            vatRate: VatRate::Fr200,
            description: 'This is product 1',
            externalReference: '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
            unit: 'piece',
            currency: Currency::Usd,
            reference: 'REF-123',
            ledgerAccountId: 42,
            substance: Substance::Goods,
        ));

        $mockClient->assertSent(function (PostProducts $sent): bool {
            return [
                'label' => 'Product 1',
                'description' => 'This is product 1',
                'external_reference' => '0e67fc3c-c632-4feb-ad34-e18ed5fbf66a',
                'price_before_tax' => '100.00',
                'vat_rate' => 'FR_200',
                'unit' => 'piece',
                'currency' => 'USD',
                'reference' => 'REF-123',
                'ledger_account_id' => 42,
                'substance' => 'goods',
            ] === $sent->body()->all();
        });
    }
}
