<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class UpdateInvoiceLine
{
    public function __construct(
        public int $id,
        public ?string $label = null,
        public int|float|null $quantity = null,
        public ?string $unit = null,
        public ?string $rawCurrencyUnitPrice = null,
        public ?VatRate $vatRate = null,
        public ?string $description = null,
        public ?int $sectionRank = null,
        public ?Discount $discount = null,
        public ?int $ledgerAccountId = null,
        public ?int $productId = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'label' => $this->label,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'vat_rate' => $this->vatRate?->value,
            'description' => $this->description,
            'section_rank' => $this->sectionRank,
            'discount' => $this->discount?->toArray(),
            'ledger_account_id' => $this->ledgerAccountId,
            'product_id' => $this->productId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
