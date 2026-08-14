<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\Products;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\Products\PutProduct;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PutProductTest extends TestCase
{
    public function testSendsAPutRequestWithOnlyTheProvidedFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 42])]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new PutProduct(id: 42, label: 'Renamed product', vatRate: VatRate::Fr55));

        $mockClient->assertSent(function (PutProduct $sent): bool {
            return Method::PUT === $sent->getMethod()
                && '/products/42' === $sent->resolveEndpoint()
                && [
                    'label' => 'Renamed product',
                    'vat_rate' => 'FR_55',
                ] === $sent->body()->all();
        });
    }
}
