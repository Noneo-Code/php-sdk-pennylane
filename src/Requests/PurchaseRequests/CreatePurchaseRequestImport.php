<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Requests\PurchaseRequests;

use Noneo\Sdk\Pennylane\Dto\Customer\Address;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Currency;
use Noneo\Sdk\Pennylane\Dto\PurchaseRequest\NewPurchaseRequestLine;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

final class CreatePurchaseRequestImport extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /** @param list<NewPurchaseRequestLine> $purchaseRequestLines */
    public function __construct(
        private readonly int $fileAttachmentId,
        private readonly string $reason,
        private readonly int $supplierId,
        private readonly string $purchaseOrderNumber,
        private readonly string $currencyAmountBeforeTax,
        private readonly string $currencyAmount,
        private readonly string $currencyTax,
        private readonly Address $deliveryAddress,
        private readonly array $purchaseRequestLines,
        private readonly ?string $estimatedDeliveryDate = null,
        private readonly ?Currency $currency = null,
        private readonly ?string $amount = null,
        private readonly ?string $tax = null,
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/purchase_requests/imports';
    }

    /** @return array<string, mixed> */
    protected function defaultBody(): array
    {
        return array_filter([
            'file_attachment_id' => $this->fileAttachmentId,
            'reason' => $this->reason,
            'estimated_delivery_date' => $this->estimatedDeliveryDate,
            'supplier_id' => $this->supplierId,
            'purchase_order_number' => $this->purchaseOrderNumber,
            'currency' => $this->currency?->value,
            'currency_amount_before_tax' => $this->currencyAmountBeforeTax,
            'currency_amount' => $this->currencyAmount,
            'amount' => $this->amount,
            'currency_tax' => $this->currencyTax,
            'tax' => $this->tax,
            'delivery_address' => $this->deliveryAddress->toArray(),
            'purchase_request_lines' => array_map(
                static fn (NewPurchaseRequestLine $line): array => $line->toArray(),
                $this->purchaseRequestLines,
            ),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
