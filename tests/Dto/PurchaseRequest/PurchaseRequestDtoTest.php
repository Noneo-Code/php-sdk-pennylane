<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Tests\Dto\PurchaseRequest;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\PurchaseRequestDto;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\PurchaseRequestStatus;
use Noneo\Sdk\Pennylane\Tooling\Schema\DocumentedBy;
use PHPUnit\Framework\TestCase;

final class PurchaseRequestDtoTest extends TestCase
{
    /** @return array<string, mixed> */
    #[DocumentedBy('getPurchaseRequest')]
    public static function documentedPayload(): array
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
            'purchase_order' => ['filename' => 'Bon-de-commande.pdf', 'url' => 'https://www.pennylane.com/Bon-de-commande.pdf'],
            'linked_invoices' => ['items' => [['id' => 42, 'url' => 'https://app.pennylane.com/api/external/v2/supplier_invoices/42']]],
            'created_at' => '2023-08-30T10:08:08.146343Z',
            'updated_at' => '2023-08-30T10:08:08.146343Z',
        ];
    }

    public function testFromArrayBuildsAPurchaseRequestDtoFromTheDocumentedPayload(): void
    {
        $purchaseRequest = PurchaseRequestDto::fromArray($this->documentedPayload());

        self::assertSame(1, $purchaseRequest->id);
        self::assertSame('PR20230001', $purchaseRequest->purchaseOrderNumber);
        self::assertSame(2, $purchaseRequest->supplierId);
        self::assertSame('https://app.pennylane.com/api/external/v2/suppliers/2', $purchaseRequest->supplierUrl);
        self::assertSame('8 rue de la paix', $purchaseRequest->deliveryAddress->address);
        self::assertSame(PurchaseRequestStatus::ToBeValidated, $purchaseRequest->status);
        self::assertSame(Currency::Eur, $purchaseRequest->currency);
        self::assertSame('I need a computer to be able to create purchase requests', $purchaseRequest->reason);
        self::assertSame('2023-12-31', $purchaseRequest->estimatedDeliveryDate);
        self::assertSame('1234.56', $purchaseRequest->amount);
        self::assertNotNull($purchaseRequest->purchaseOrder);
        self::assertSame('Bon-de-commande.pdf', $purchaseRequest->purchaseOrder->filename);
        self::assertCount(1, $purchaseRequest->linkedInvoices);
        self::assertSame(42, $purchaseRequest->linkedInvoices[0]->id);
        self::assertSame('2023-08-30T10:08:08.146343Z', $purchaseRequest->createdAt);
        self::assertSame('2023-08-30T10:08:08.146343Z', $purchaseRequest->updatedAt);
    }

    public function testFromArrayAllowsNullableFieldsToBeNull(): void
    {
        $payload = $this->documentedPayload();
        $payload['purchase_order_number'] = null;
        $payload['estimated_delivery_date'] = null;
        $payload['purchase_order'] = null;

        $purchaseRequest = PurchaseRequestDto::fromArray($payload);

        self::assertNull($purchaseRequest->purchaseOrderNumber);
        self::assertNull($purchaseRequest->estimatedDeliveryDate);
        self::assertNull($purchaseRequest->purchaseOrder);
    }
}
