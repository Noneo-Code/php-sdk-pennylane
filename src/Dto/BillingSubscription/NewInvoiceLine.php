<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\BillingSubscription;

use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\Discount;
use Noneo\Sdk\Pennylane\Dto\CustomerInvoice\VatRate;

final readonly class NewInvoiceLine
{
    public function __construct(
        public string $label,
        public int|float $quantity,
        public string $unit,
        public string $rawCurrencyUnitPrice,
        public VatRate $vatRate,
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
            'label' => $this->label,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'vat_rate' => $this->vatRate->value,
            'description' => $this->description,
            'section_rank' => $this->sectionRank,
            'discount' => $this->discount?->toArray(),
            'ledger_account_id' => $this->ledgerAccountId,
            'product_id' => $this->productId,
        ], static fn (mixed $value): bool => null !== $value);
    }
}
