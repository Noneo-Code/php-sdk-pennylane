<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Requests\PurchaseRequests;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\NewPurchaseRequestLine;
use Noneo\Sdk\Pennylane\Pennylane;
use Noneo\Sdk\Pennylane\Requests\PurchaseRequests\CreatePurchaseRequestImport;
use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class CreatePurchaseRequestImportTest extends TestCase
{
    public function testSendsAPostRequestWithTheRequiredFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreatePurchaseRequestImport(
            fileAttachmentId: 42,
            reason: 'I need a computer to be able to create purchase requests',
            supplierId: 456,
            purchaseOrderNumber: 'PR20230001',
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            deliveryAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            purchaseRequestLines: [
                new NewPurchaseRequestLine(
                    currencyAmount: '120.00',
                    currencyTax: '20.00',
                    label: 'Demo label',
                    quantity: 12,
                    unitPrice: '8.34',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                ),
            ],
        ));

        $mockClient->assertSent(function (CreatePurchaseRequestImport $sent): bool {
            return Method::POST === $sent->getMethod()
                && '/purchase_requests/imports' === $sent->resolveEndpoint()
                && [
                    'file_attachment_id' => 42,
                    'reason' => 'I need a computer to be able to create purchase requests',
                    'supplier_id' => 456,
                    'purchase_order_number' => 'PR20230001',
                    'currency_amount_before_tax' => '100.00',
                    'currency_amount' => '120.00',
                    'currency_tax' => '20.00',
                    'delivery_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
                    'purchase_request_lines' => [[
                        'currency_amount' => '120.00',
                        'currency_tax' => '20.00',
                        'label' => 'Demo label',
                        'quantity' => 12,
                        'unit_price' => '8.34',
                        'unit' => 'piece',
                        'vat_rate' => 'FR_200',
                    ]],
                ] === $sent->body()->all();
        });
    }

    public function testSendsTheOptionalFields(): void
    {
        $pennylane = new Pennylane('a-token');
        $mockClient = new MockClient([MockResponse::make(['id' => 1], 201)]);
        $pennylane->connector()->withMockClient($mockClient);

        $pennylane->connector()->send(new CreatePurchaseRequestImport(
            fileAttachmentId: 42,
            reason: 'I need a computer to be able to create purchase requests',
            supplierId: 456,
            purchaseOrderNumber: 'PR20230001',
            currencyAmountBeforeTax: '100.00',
            currencyAmount: '120.00',
            currencyTax: '20.00',
            deliveryAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            purchaseRequestLines: [],
            estimatedDeliveryDate: '2023-08-30',
            currency: Currency::Eur,
            amount: '120.00',
            tax: '20.00',
        ));

        $mockClient->assertSent(function (CreatePurchaseRequestImport $sent): bool {
            $body = $sent->body()->all();

            return '2023-08-30' === $body['estimated_delivery_date']
                && 'EUR' === $body['currency']
                && '120.00' === $body['amount']
                && '20.00' === $body['tax']
                && [] === $body['purchase_request_lines'];
        });
    }
}
