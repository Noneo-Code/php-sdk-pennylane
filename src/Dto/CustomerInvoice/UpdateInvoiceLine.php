<?php

declare(strict_types=1);

namespace Noneo\Sdk\Pennylane\Dto\CustomerInvoice;

final readonly class UpdateInvoiceLine
{
    public function __construct(
        public int $id,
        public ?string $label = null,
        public int|string|null $quantity = null,
        public ?int $ledgerAccountId = null,
        public ?string $rawCurrencyUnitPrice = null,
        public ?string $unit = null,
        public ?VatRate $vatRate = null,
        public ?string $description = null,
        public ?int $productId = null,
        public ?Discount $discount = null,
        public ?int $sectionRank = null,
        public ?ImputationDates $imputationDates = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'label' => $this->label,
            'quantity' => $this->quantity,
            'ledger_account_id' => $this->ledgerAccountId,
            'raw_currency_unit_price' => $this->rawCurrencyUnitPrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate?->value,
            'description' => $this->description,
            'product_id' => $this->productId,
            'discount' => $this->discount?->toArray(),
            'section_rank' => $this->sectionRank,
            'imputation_dates' => $this->imputationDates?->toArray(),
        ], static fn (mixed $value): bool => null !== $value);
    }
}
