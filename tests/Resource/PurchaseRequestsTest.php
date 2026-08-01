<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Resource;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\NewPurchaseRequestLine;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\PurchaseRequestDto;
use Noneo\Sdk\Pennylane\Http\CursorPage;
use Noneo\Sdk\Pennylane\Pennylane;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class PurchaseRequestsTest extends TestCase
{
    /** @return array<string, mixed> */
    private function documentedPurchaseRequestPayload(): array
    {
        return [
            'id' => 1,
            'purchase_order_number' => 'PR20230001',
            'supplier' => ['id' => 2, 'url' => 'https://app.pennylane.com/api/external/v2/suppliers/2'],
            'delivery_address' => ['address' => '8 rue de la paix', 'postal_code' => '75002', 'city' => 'Paris', 'country_alpha2' => 'FR'],
            'status' => 'to_be_validated',
            'currency' => 'EUR',
            'reason' => 'I need a computer to be able to create purchase requests',
            'estimated_delivery_date' => '2023-12-31',
            'amount' => '1234.56',
            'currency_amount' => '1234.56',
            'currency_amount_before_tax' => '1200.23',
            'exchange_rate' => '1.0',
            'currency_tax' => '34.33',
            'tax' => '34.33',
            'purchase_order' => null,
            'linked_invoices' => ['items' => []],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testListReturnsACursorPageOfPurchaseRequestDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make([
            'items' => [$this->documentedPurchaseRequestPayload()],
            'has_more' => false,
            'next_cursor' => null,
        ])]);
        $pennylane->withMockClient($mockClient);

        $page = $pennylane->purchaseRequests()->list();

        self::assertInstanceOf(CursorPage::class, $page);
        self::assertCount(1, $page->items);
        self::assertInstanceOf(PurchaseRequestDto::class, $page->items[0]);
        self::assertSame(1, $page->items[0]->id);
    }

    public function testGetReturnsAPurchaseRequestDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPurchaseRequestPayload())]);
        $pennylane->withMockClient($mockClient);

        $purchaseRequest = $pennylane->purchaseRequests()->get(1);

        self::assertSame(1, $purchaseRequest->id);
    }

    public function testImportReturnsAPurchaseRequestDto(): void
    {
        $pennylane = new Pennylane(new TokenAuthenticator('a-token'));
        $mockClient = new MockClient([MockResponse::make($this->documentedPurchaseRequestPayload(), 201)]);
        $pennylane->withMockClient($mockClient);

        $purchaseRequest = $pennylane->purchaseRequests()->import(
            fileAttachmentId: 42,
            reason: 'I need a computer to be able to create purchase requests',
            supplierId: 2,
            purchaseOrderNumber: 'PR20230001',
            currencyAmountBeforeTax: '1200.23',
            currencyAmount: '1234.56',
            currencyTax: '34.33',
            deliveryAddress: new Address('8 rue de la paix', '75002', 'Paris', 'FR'),
            purchaseRequestLines: [
                new NewPurchaseRequestLine(
                    currencyAmount: '1234.56',
                    currencyTax: '34.33',
                    label: 'Demo label',
                    quantity: 1,
                    unitPrice: '1234.56',
                    unit: 'piece',
                    vatRate: VatRate::Fr200,
                ),
            ],
        );

        self::assertInstanceOf(PurchaseRequestDto::class, $purchaseRequest);
        self::assertSame(1, $purchaseRequest->id);
    }
}
