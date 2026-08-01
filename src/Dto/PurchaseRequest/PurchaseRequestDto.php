<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\PurchaseRequest;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;

final readonly class PurchaseRequestDto
{
    /** @param list<LinkedInvoiceDto> $linkedInvoices */
    public function __construct(
        public int $id,
        public ?string $purchaseOrderNumber,
        public int $supplierId,
        public string $supplierUrl,
        public DeliveryAddressDto $deliveryAddress,
        public PurchaseRequestStatus $status,
        public Currency $currency,
        public string $reason,
        public ?string $estimatedDeliveryDate,
        public string $amount,
        public string $currencyAmount,
        public string $currencyAmountBeforeTax,
        public string $exchangeRate,
        public string $currencyTax,
        public string $tax,
        public ?PurchaseOrderDto $purchaseOrder,
        public array $linkedInvoices,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    /** @param mixed[] $body */
    public static function fromArray(array $body): self
    {
        $id = $body['id'] ?? null;
        $supplier = $body['supplier'] ?? null;
        $deliveryAddress = $body['delivery_address'] ?? null;
        $status = $body['status'] ?? null;
        $currency = $body['currency'] ?? null;
        $reason = $body['reason'] ?? null;
        $amount = $body['amount'] ?? null;
        $currencyAmount = $body['currency_amount'] ?? null;
        $currencyAmountBeforeTax = $body['currency_amount_before_tax'] ?? null;
        $exchangeRate = $body['exchange_rate'] ?? null;
        $currencyTax = $body['currency_tax'] ?? null;
        $tax = $body['tax'] ?? null;
        $purchaseOrder = $body['purchase_order'] ?? null;
        $linkedInvoices = $body['linked_invoices'] ?? null;
        $createdAt = $body['created_at'] ?? null;
        $updatedAt = $body['updated_at'] ?? null;

        if (!is_int($id)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un entier "id".');
        }

        if (!is_array($supplier) || !is_int($supplier['id'] ?? null) || !is_string($supplier['url'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "supplier" avec un "id" et une "url".');
        }

        if (!is_array($deliveryAddress)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un objet "delivery_address".');
        }

        if (!is_string($status)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir un "status" textuel.');
        }

        if (!is_string($currency)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "currency" textuelle.');
        }

        if (!is_string($reason)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir une "reason" textuelle.');
        }

        if (!is_string($amount) || !is_string($currencyAmount) || !is_string($currencyAmountBeforeTax) || !is_string($exchangeRate) || !is_string($currencyTax) || !is_string($tax)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir les montants "amount", "currency_amount", "currency_amount_before_tax", "exchange_rate", "currency_tax" et "tax" sous forme de texte.');
        }

        if (null !== $purchaseOrder && !is_array($purchaseOrder)) {
            throw new \InvalidArgumentException('Le "purchase_order" de la réponse Pennylane doit être un objet ou nul.');
        }

        if (!is_array($linkedInvoices) || !is_array($linkedInvoices['items'] ?? null)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir des "linked_invoices" avec un tableau "items".');
        }

        if (!is_string($createdAt) || !is_string($updatedAt)) {
            throw new \InvalidArgumentException('Le corps de la réponse Pennylane doit contenir "created_at" et "updated_at" sous forme de texte.');
        }

        $mappedLinkedInvoices = [];

        foreach ($linkedInvoices['items'] as $linkedInvoice) {
            if (!is_array($linkedInvoice)) {
                throw new \InvalidArgumentException('Les "linked_invoices" de la réponse Pennylane doivent être des objets.');
            }

            $mappedLinkedInvoices[] = LinkedInvoiceDto::fromArray($linkedInvoice);
        }

        return new self(
            id: $id,
            purchaseOrderNumber: is_string($body['purchase_order_number'] ?? null) ? $body['purchase_order_number'] : null,
            supplierId: $supplier['id'],
            supplierUrl: $supplier['url'],
            deliveryAddress: DeliveryAddressDto::fromArray($deliveryAddress),
            status: PurchaseRequestStatus::from($status),
            currency: Currency::from($currency),
            reason: $reason,
            estimatedDeliveryDate: is_string($body['estimated_delivery_date'] ?? null) ? $body['estimated_delivery_date'] : null,
            amount: $amount,
            currencyAmount: $currencyAmount,
            currencyAmountBeforeTax: $currencyAmountBeforeTax,
            exchangeRate: $exchangeRate,
            currencyTax: $currencyTax,
            tax: $tax,
            purchaseOrder: is_array($purchaseOrder) ? PurchaseOrderDto::fromArray($purchaseOrder) : null,
            linkedInvoices: $mappedLinkedInvoices,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
